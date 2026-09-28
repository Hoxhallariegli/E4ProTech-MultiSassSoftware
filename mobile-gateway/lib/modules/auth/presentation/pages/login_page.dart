import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../../../core/widgets/premium_widgets.dart';
import '../../../../core/theme/app_theme.dart';
import '../../../../services/api_service.dart';
import '../../../../services/auth_service.dart';
import '../../../../core/localization/locale_cubit.dart';
import '../../../../core/branding/branding_cubit.dart';
import '../../../../l10n/core_localization.dart';
import '../../../dashboard/presentation/pages/app_shell.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController(text: 'demo@e4protech.com');
  final _passwordController = TextEditingController(text: 'E4ProTech');
  bool _loading = false;
  bool _obscurePassword = true;
  String _currentServer = 'Loading...';

  @override
  void initState() {
    super.initState();
    _loadServerUrl();
  }

  void _loadServerUrl() async {
    final url = await ApiService.serverUrl;
    if (mounted) {
      setState(() => _currentServer = url);
    }
  }

  void _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _loading = true);

    try {
      final response = await ApiService.post('/login', {
        'email': _emailController.text,
        'password': _passwordController.text,
        'device_name': 'Mobile APK',
      });

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final token = data['token'];
        final userData = data['user'];

        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', token);
        await prefs.setString('user_data', jsonEncode(userData));

        // Refresh permissions in AuthService
        await AuthService.instance.init();

        // Update Dynamic Branding
        if (userData['business'] != null && mounted) {
          context.read<BrandingCubit>().updateBranding(userData['business']);
        }

        if (mounted) {
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(builder: (_) => const AppShell()),
          );
        }
      } else {
        final error = ApiService.extractErrorMessage(response);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(error), backgroundColor: Colors.redAccent),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gabim lidhjeje: $e'), backgroundColor: Colors.redAccent),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      backgroundColor: theme.colorScheme.surface,
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(28.0),
          child: Container(
            constraints: const BoxConstraints(maxWidth: 400),
            child: Form(
              key: _formKey,
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Container(
                    height: 80,
                    width: 80,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [theme.colorScheme.primary, theme.colorScheme.secondary],
                      ),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.bolt, color: Colors.white, size: 45),
                  ),
                  const SizedBox(height: 24),
                  Text(
                    coreTr(context, 'auth.welcome'),
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w900, letterSpacing: -0.5),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    coreTr(context, 'auth.subtitle'),
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 13, color: Colors.grey, fontWeight: FontWeight.w500),
                  ),
                  const SizedBox(height: 12),
                  Center(
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: theme.colorScheme.primary.withOpacity(0.08),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: theme.colorScheme.primary.withOpacity(0.2)),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.dns_outlined, size: 14, color: theme.colorScheme.primary),
                          const SizedBox(width: 8),
                          Text(
                            'Server: $_currentServer',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: theme.colorScheme.primary,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 36),
                  PremiumTextField(
                    controller: _emailController,
                    label: coreTr(context, 'auth.email'),
                    hint: coreTr(context, 'auth.email_hint'),
                    keyboardType: TextInputType.emailAddress,
                    validator: (v) => (v == null || v.isEmpty) ? coreTr(context, 'auth.email_required') : null,
                  ),
                  const SizedBox(height: 16),
                  PremiumTextField(
                    controller: _passwordController,
                    label: coreTr(context, 'auth.password'),
                    hint: coreTr(context, 'auth.password_hint'),
                    obscureText: _obscurePassword,
                    validator: (v) => (v == null || v.isEmpty) ? coreTr(context, 'auth.password_required') : null,
                    suffixIcon: IconButton(
                      icon: Icon(_obscurePassword ? Icons.visibility_off_outlined : Icons.visibility_outlined, size: 20),
                      onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Align(
                    alignment: Alignment.centerRight,
                    child: TextButton(
                      onPressed: () {},
                      child: Text(
                        coreTr(context, 'auth.forgot'),
                        style: TextStyle(fontSize: 13, color: theme.colorScheme.primary, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                  PremiumButton(
                    onPressed: _handleLogin,
                    label: _loading ? coreTr(context, 'auth.authenticating') : coreTr(context, 'auth.sign_in'),
                    icon: Icons.login_rounded,
                    loading: _loading,
                    expand: true,
                  ),
                  const SizedBox(height: 24),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Text(
                        'Powered by ',
                        style: TextStyle(fontSize: 12, color: Colors.grey),
                      ),
                      Text(
                        'E4ProTech Engine',
                        style: TextStyle(fontSize: 12, color: theme.colorScheme.primary, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }
}
