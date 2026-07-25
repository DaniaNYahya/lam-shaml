class ApiException implements Exception {
  ApiException(this.message, {this.statusCode});

  final String message;
  final int? statusCode;

  @override
  String toString() => message;
}

class Account {
  const Account({
    required this.id,
    required this.fullName,
    required this.phone,
    required this.email,
    required this.city,
    required this.role,
    required this.status,
  });

  final int id;
  final String fullName;
  final String phone;
  final String email;
  final String city;
  final String role;
  final String status;

  factory Account.fromJson(Map<String, dynamic> json) => Account(
        id: _asInt(json['account_id'] ?? json['id']),
        fullName: json['full_name']?.toString() ?? '',
        phone: json['phone']?.toString() ?? '',
        email: json['email']?.toString() ?? '',
        city: json['city']?.toString() ?? '',
        role: json['role']?.toString() ?? 'user',
        status: json['status']?.toString() ?? 'active',
      );
}

class AuthSession {
  const AuthSession({required this.account, required this.token});

  final Account account;
  final String token;
}

class FamilyRequest {
  const FamilyRequest({
    required this.id,
    required this.type,
    required this.status,
    required this.priority,
    required this.fullName,
    required this.age,
    required this.gender,
    required this.city,
    required this.area,
    required this.description,
    required this.matchPercent,
    this.contactPhone,
  });

  final int id;
  final String type;
  final String status;
  final String priority;
  final String fullName;
  final int age;
  final String gender;
  final String city;
  final String area;
  final String description;
  final double matchPercent;
  final String? contactPhone;

  factory FamilyRequest.fromJson(Map<String, dynamic> json) => FamilyRequest(
        id: _asInt(json['request_id'] ?? json['id']),
        type: json['request_type']?.toString() ?? '',
        status: json['status']?.toString() ?? '',
        priority: json['priority']?.toString() ?? '',
        fullName: json['full_name']?.toString() ?? '',
        age: _asInt(json['age']),
        gender: json['gender']?.toString() ?? '',
        city: json['city']?.toString() ?? json['original_city']?.toString() ?? '',
        area: json['area']?.toString() ?? '',
        description: json['description']?.toString() ?? '',
        matchPercent: _asDouble(json['match_percent'] ?? json['total_score']),
        contactPhone: json['contact_phone']?.toString(),
      );
}

class NotificationItem {
  const NotificationItem({
    required this.id,
    required this.message,
    required this.type,
    required this.isRead,
    required this.createdAt,
  });

  final int id;
  final String message;
  final String type;
  final bool isRead;
  final String createdAt;

  factory NotificationItem.fromJson(Map<String, dynamic> json) => NotificationItem(
        id: _asInt(json['notification_id'] ?? json['id']),
        message: json['message']?.toString() ?? '',
        type: json['type']?.toString() ?? 'info',
        isRead: _asInt(json['is_read']) == 1 || json['is_read'] == true,
        createdAt: json['created_at']?.toString() ?? '',
      );
}

class DashboardStats {
  const DashboardStats({
    required this.requests,
    required this.missing,
    required this.found,
    required this.matches,
    required this.notifications,
  });

  final int requests;
  final int missing;
  final int found;
  final int matches;
  final int notifications;

  factory DashboardStats.fromJson(Map<String, dynamic> json) => DashboardStats(
        requests: _asInt(json['requests']),
        missing: _asInt(json['missing']),
        found: _asInt(json['found']),
        matches: _asInt(json['matches']),
        notifications: _asInt(json['notifications']),
      );
}

int _asInt(Object? value) {
  if (value is int) return value;
  if (value is num) return value.toInt();
  return int.tryParse(value?.toString() ?? '') ?? 0;
}

double _asDouble(Object? value) {
  if (value is double) return value;
  if (value is num) return value.toDouble();
  return double.tryParse(value?.toString() ?? '') ?? 0;
}
