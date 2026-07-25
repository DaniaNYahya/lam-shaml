import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:provider/provider.dart';

import 'core/app_state.dart';
import 'core/app_strings.dart';
import 'features/screens.dart';
import 'services/lam_shaml_api.dart';

void main() {
  runApp(
    ChangeNotifierProvider(
      create: (_) => AppState(LamShamlApi()),
      child: const LamShamlApp(),
    ),
  );
}

class LamShamlApp extends StatelessWidget {
  const LamShamlApp({super.key});

  @override
  Widget build(BuildContext context) {
    final state = context.watch<AppState>();
    return MaterialApp(
      title: 'Lam Shaml',
      debugShowCheckedModeBanner: false,
      locale: state.locale,
      supportedLocales: AppStrings.supportedLocales,
      localizationsDelegates: const [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xff227c70),
          brightness: Brightness.light,
        ),
        scaffoldBackgroundColor: const Color(0xfff7f9f6),
        cardTheme: const CardThemeData(
          elevation: 0,
          margin: EdgeInsets.symmetric(vertical: 6),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.all(Radius.circular(8)),
          ),
        ),
      ),
      builder: (context, child) {
        final rtl = state.locale.languageCode == 'ar';
        return Directionality(
          textDirection: rtl ? TextDirection.rtl : TextDirection.ltr,
          child: child ?? const SizedBox.shrink(),
        );
      },
      routes: {
        '/': (_) => const SplashScreen(),
        '/onboarding': (_) => const OnboardingScreen(),
        '/home': (_) => const HomeScreen(),
        '/auth': (_) => const AuthScreen(),
        '/dashboard': (_) => const UserDashboardScreen(),
        '/missing': (_) => const RequestFormScreen(type: 'missing'),
        '/found': (_) => const RequestFormScreen(type: 'found'),
        '/search': (_) => const SearchScreen(),
        '/notifications': (_) => const NotificationsScreen(),
        '/profile': (_) => const ProfileSettingsScreen(),
        '/admin': (_) => const AdminScreen(),
      },
    );
  }
}
