import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../models/models.dart';
import '../services/lam_shaml_api.dart';

class AppState extends ChangeNotifier {
  AppState(this.api);

  final LamShamlApi api;
  String? token;
  Account? account;
  Locale locale = const Locale('ar');
  bool initialized = false;

  bool get isAuthenticated => token != null && account != null;
  bool get isAdmin => account?.role == 'admin';

  Future<void> initialize() async {
    final prefs = await SharedPreferences.getInstance();
    token = prefs.getString('api_token');
    locale = Locale(prefs.getString('locale') ?? 'ar');
    if (token != null) {
      api.token = token;
      try {
        account = await api.me();
      } catch (_) {
        token = null;
        api.token = null;
        await prefs.remove('api_token');
      }
    }
    initialized = true;
    notifyListeners();
  }

  Future<void> login(String email, String password) async {
    final session = await api.login(email: email, password: password);
    token = session.token;
    account = session.account;
    api.token = token;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('api_token', token!);
    notifyListeners();
  }

  Future<void> register({
    required String fullName,
    required String phone,
    required String email,
    required String password,
    required String city,
  }) async {
    final session = await api.register(
      fullName: fullName,
      phone: phone,
      email: email,
      password: password,
      city: city,
    );
    token = session.token;
    account = session.account;
    api.token = token;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('api_token', token!);
    notifyListeners();
  }

  Future<void> logout() async {
    try {
      await api.logout();
    } finally {
      token = null;
      account = null;
      api.token = null;
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('api_token');
      notifyListeners();
    }
  }

  Future<void> setLocale(Locale value) async {
    locale = value;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('locale', value.languageCode);
    notifyListeners();
  }
}
