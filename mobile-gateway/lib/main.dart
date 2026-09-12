import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:mobile_gateway/core/theme/app_theme.dart';
import 'package:mobile_gateway/core/localization/locale_cubit.dart';
import 'package:mobile_gateway/modules/auth/presentation/pages/login_page.dart';
import 'package:mobile_gateway/modules/dashboard/presentation/pages/app_shell.dart';

final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final prefs = await SharedPreferences.getInstance();
  final token = prefs.getString('auth_token');
  runApp(
    BlocProvider(
      create: (context) => LocaleCubit(),
      child: MyApp(isLoggedIn: token != null && token.isNotEmpty),
    ),
  );
}

class MyApp extends StatelessWidget {
  final bool isLoggedIn;
  const MyApp({super.key, required this.isLoggedIn});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<LocaleCubit, LocaleState>(
      builder: (context, state) {
        return MaterialApp(
          title: 'LaraFlutter Gateway',
          navigatorKey: navigatorKey,
          debugShowCheckedModeBanner: false,
          theme: AppTheme.light(),
          darkTheme: AppTheme.dark(),
          themeMode: ThemeMode.system,
          locale: state.locale,
          supportedLocales: state.supportedLocales,
          localizationsDelegates: const [
            GlobalMaterialLocalizations.delegate,
            GlobalWidgetsLocalizations.delegate,
            GlobalCupertinoLocalizations.delegate,
          ],
          home: isLoggedIn ? const AppShell() : const LoginPage(),
        );
      },
    );
  }
}
