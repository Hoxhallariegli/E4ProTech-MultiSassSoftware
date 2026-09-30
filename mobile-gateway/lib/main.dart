import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:mobile_gateway/core/theme/app_theme.dart';
import 'package:mobile_gateway/core/theme/theme_cubit.dart';
import 'package:mobile_gateway/core/localization/locale_cubit.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';
import 'package:mobile_gateway/core/notifications/push_service.dart';
import 'package:mobile_gateway/modules/auth/presentation/pages/login_page.dart';
import 'package:mobile_gateway/modules/dashboard/presentation/pages/app_shell.dart';
import 'package:mobile_gateway/services/auth_service.dart';

final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await AuthService.instance.init();

  // Run PushService init in background without blocking runApp
  PushService.initialize().timeout(const Duration(seconds: 3)).catchError((e) {
    debugPrint('PushService background init error: $e');
  });

  final prefs = await SharedPreferences.getInstance();
  final token = prefs.getString('auth_token');

  runApp(
    MultiBlocProvider(
      providers: [
        BlocProvider(create: (context) => LocaleCubit()),
        BlocProvider(create: (context) => BrandingCubit()),
        BlocProvider(create: (context) => ThemeCubit()),
      ],
      child: MyApp(isLoggedIn: token != null && token.isNotEmpty),
    ),
  );
}

class MyApp extends StatelessWidget {
  final bool isLoggedIn;
  const MyApp({super.key, required this.isLoggedIn});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<ThemeCubit, ThemeMode>(
      builder: (context, themeMode) {
        return BlocBuilder<BrandingCubit, BrandingState>(
          builder: (context, branding) {
            return BlocBuilder<LocaleCubit, LocaleState>(
              builder: (context, localeState) {
                return MaterialApp(
                  title: branding.appName,
                  navigatorKey: navigatorKey,
                  debugShowCheckedModeBanner: false,
                  theme: AppTheme.light().copyWith(
                    primaryColor: branding.primaryColor,
                    colorScheme: AppTheme.light().colorScheme.copyWith(
                      primary: branding.primaryColor,
                      secondary: branding.primaryColor,
                    ),
                  ),
                  darkTheme: AppTheme.dark().copyWith(
                    primaryColor: branding.primaryColor,
                    colorScheme: AppTheme.dark().colorScheme.copyWith(
                      primary: branding.primaryColor,
                      secondary: branding.primaryColor,
                    ),
                  ),
                  themeMode: themeMode,
                  locale: localeState.locale,
                  supportedLocales: localeState.supportedLocales,
                  localizationsDelegates: const [
                    GlobalMaterialLocalizations.delegate,
                    GlobalWidgetsLocalizations.delegate,
                    GlobalCupertinoLocalizations.delegate,
                  ],
                  routes: {
                    '/dashboard': (context) => const AppShell(),
                  },
                  home: isLoggedIn ? const AppShell() : const LoginPage(),
                );
              },
            );
          },
        );
      },
    );
  }
}
