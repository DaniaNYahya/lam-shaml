import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:flutter_image_compress/flutter_image_compress.dart';
import 'package:http/http.dart' as http;
import 'package:http_parser/http_parser.dart';

import '../models/models.dart';

class ApiConfig {
  static const defaultBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2/lamshaml/backend/public',
  );
}

class LamShamlApi {
  LamShamlApi({http.Client? client, String? baseUrl})
      : _client = client ?? http.Client(),
        baseUrl = baseUrl ?? ApiConfig.defaultBaseUrl;

  final http.Client _client;
  final String baseUrl;
  String? token;

  Map<String, String> get _headers => {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      };

  Future<AuthSession> login({required String email, required String password}) async {
    final data = await _post('/auth/login', {'email': email, 'password': password});
    return AuthSession(
      account: Account.fromJson(data['account']),
      token: data['token'].toString(),
    );
  }

  Future<AuthSession> register({
    required String fullName,
    required String phone,
    required String email,
    required String password,
    required String city,
  }) async {
    final data = await _post('/auth/register', {
      'full_name': fullName,
      'phone': phone,
      'email': email,
      'password': password,
      'city': city,
    });
    return AuthSession(
      account: Account.fromJson(data['account']),
      token: data['token'].toString(),
    );
  }

  Future<Account> me() async {
    final data = await _get('/auth/me');
    return Account.fromJson(data['account']);
  }

  Future<void> logout() async {
    await _post('/auth/logout', {});
  }

  Future<DashboardStats> stats() async {
    final data = await _get('/stats');
    return DashboardStats.fromJson(data['stats']);
  }

  Future<DashboardStats> myDashboard() async {
    final data = await _get('/requests/mine');
    return DashboardStats.fromJson(data['stats']);
  }

  Future<List<FamilyRequest>> myRequests() async {
    final data = await _get('/requests/mine');
    return _items(data).map(FamilyRequest.fromJson).toList();
  }

  Future<FamilyRequest> requestDetails(int id) async {
    final data = await _get('/requests/$id');
    return FamilyRequest.fromJson(data['request']);
  }

  Future<Map<String, dynamic>> createRequest(
    Map<String, String> fields, {
    File? image,
  }) async {
    final uri = Uri.parse('$baseUrl/requests');
    final request = http.MultipartRequest('POST', uri);
    request.headers['Accept'] = 'application/json';
    if (token != null) request.headers['Authorization'] = 'Bearer $token';
    request.fields.addAll(fields);

    if (image != null) {
      final compressed = await FlutterImageCompress.compressWithFile(
        image.path,
        minWidth: 1200,
        minHeight: 1200,
        quality: 72,
      );
      if (compressed != null) {
        request.files.add(http.MultipartFile.fromBytes(
          'image',
          compressed,
          filename: 'request.jpg',
          contentType: MediaType('image', 'jpeg'),
        ));
      }
    }

    final response = await http.Response.fromStream(await request.send());
    return _decode(response);
  }

  Future<List<FamilyRequest>> search(Map<String, String> params) async {
    final data = await _get('/search', params: params);
    return _items(data).map(FamilyRequest.fromJson).toList();
  }

  Future<void> reportPossibleMatch({
    required int requestId,
    required int matchedRequestId,
    required String notes,
    required String contactPhone,
  }) async {
    await _post('/matches/report', {
      'request_id': requestId,
      'matched_request_id': matchedRequestId,
      'notes': notes,
      'contact_phone': contactPhone,
    });
  }

  Future<List<NotificationItem>> notifications() async {
    final data = await _get('/notifications');
    return _items(data).map(NotificationItem.fromJson).toList();
  }

  Future<void> markNotificationRead(int id) async {
    await _post('/notifications/$id/read', {});
  }

  Future<Map<String, dynamic>> adminDashboard() => _get('/admin/dashboard');
  Future<Map<String, dynamic>> adminUsers() => _get('/admin/users');
  Future<Map<String, dynamic>> adminRequests() => _get('/admin/requests');
  Future<Map<String, dynamic>> adminMatches() => _get('/admin/matches');
  Future<Map<String, dynamic>> adminReports() => _get('/admin/reports');

  Future<void> updateUserStatus(int id, String status) async {
    await _post('/admin/users/$id/status', {'status': status});
  }

  Future<void> updateRequestStatus(int id, String status) async {
    await _post('/admin/requests/$id/status', {'status': status});
  }

  Future<void> updateMatchStatus(int id, String status) async {
    await _post('/admin/matches/$id/status', {'status': status});
  }

  Future<Map<String, dynamic>> _get(String path, {Map<String, String>? params}) async {
    final uri = Uri.parse('$baseUrl$path').replace(queryParameters: params);
    final response = await _client.get(uri, headers: _headers).timeout(const Duration(seconds: 20));
    return _decode(response);
  }

  Future<Map<String, dynamic>> _post(String path, Map<String, dynamic> body) async {
    final response = await _client
        .post(Uri.parse('$baseUrl$path'), headers: _headers, body: jsonEncode(body))
        .timeout(const Duration(seconds: 20));
    return _decode(response);
  }

  Map<String, dynamic> _decode(http.Response response) {
    final Map<String, dynamic> payload =
        response.body.isEmpty ? <String, dynamic>{} : jsonDecode(response.body);
    if (response.statusCode >= 400 || payload['success'] == false) {
      throw ApiException(
        payload['message']?.toString() ?? 'Server error',
        statusCode: response.statusCode,
      );
    }
    return (payload['data'] as Map<String, dynamic>?) ?? payload;
  }

  List<Map<String, dynamic>> _items(Map<String, dynamic> data) {
    return ((data['items'] ?? data['requests'] ?? data['notifications']) as List? ?? [])
        .cast<Map<String, dynamic>>();
  }
}
