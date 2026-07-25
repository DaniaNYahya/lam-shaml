import 'package:flutter/widgets.dart';

class AppStrings {
  static const supportedLocales = [Locale('ar'), Locale('en')];

  static const Map<String, Map<String, String>> _values = {
    'ar': {
      'appName': 'لم شمل',
      'home': 'الرئيسية',
      'login': 'تسجيل الدخول',
      'register': 'إنشاء حساب',
      'logout': 'تسجيل الخروج',
      'dashboard': 'لوحتي',
      'missing': 'بلاغ مفقود',
      'found': 'بلاغ موجود',
      'search': 'البحث',
      'notifications': 'الإشعارات',
      'profile': 'الملف الشخصي',
      'admin': 'الإدارة',
      'submit': 'إرسال',
      'save': 'حفظ',
      'details': 'التفاصيل',
      'possibleMatch': 'تطابق محتمل',
      'noData': 'لا توجد بيانات بعد',
      'noInternet': 'تعذر الاتصال بالخادم',
      'loading': 'جار التحميل...',
      'privacy': 'سياسة الخصوصية',
    },
    'en': {
      'appName': 'Lam Shaml',
      'home': 'Home',
      'login': 'Login',
      'register': 'Register',
      'logout': 'Logout',
      'dashboard': 'Dashboard',
      'missing': 'Missing report',
      'found': 'Found report',
      'search': 'Search',
      'notifications': 'Notifications',
      'profile': 'Profile',
      'admin': 'Admin',
      'submit': 'Submit',
      'save': 'Save',
      'details': 'Details',
      'possibleMatch': 'Possible match',
      'noData': 'No data yet',
      'noInternet': 'Could not reach the server',
      'loading': 'Loading...',
      'privacy': 'Privacy policy',
    },
  };

  static String of(BuildContext context, String key) {
    final code = Localizations.localeOf(context).languageCode;
    return _values[code]?[key] ?? _values['ar']![key] ?? key;
  }
}
