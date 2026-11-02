import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../../core/widgets/premium_widgets.dart';
import '../../../../core/branding/branding_cubit.dart';
import '../../../../l10n/core_localization.dart';
import '../../../../services/api_service.dart';

class ForgotPasswordPage extends StatefulWidget {
  const ForgotPasswordPage({super.key});

  @override
  State<ForgotPasswordPage> createState() => _ForgotPasswordPageState();
}

class _ForgotPasswordPageState extends State<ForgotPasswordPage> {
  final _emailFormKey = GlobalKey<FormState>();
  final _resetFormKey = GlobalKey<FormState>();

  final _emailController = TextEditingController();
  final _resetEmailController = TextEditingController();
  final _tokenController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _loading = false;
  bool _resetLoading = false;
  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;
  bool _linkSent = false;
  bool _showTokenReset = false;

  void _handleSendResetLink() async {
    if (!_emailFormKey.currentState!.validate()) return;

    setState(() => _loading = true);

    try {
      final response = await ApiService.post('/forgot-password', {
        'email': _emailController.text.trim(),
      });

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final msg = data['message'] ?? 'Lidhja për rivendosjen e fjalëkalimit u dërgua me sukses.';

        if (mounted) {
          setState(() {
            _linkSent = true;
            _resetEmailController.text = _emailController.text.trim();
          });
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(msg), backgroundColor: Colors.green),
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
          SnackBar(content: Text('Gabim: $e'), backgroundColor: Colors.redAccent),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _handleResetPassword() async {
    if (!_resetFormKey.currentState!.validate()) return;

    setState(() => _resetLoading = true);

    try {
      final response = await ApiService.post('/reset-password', {
        'email': _resetEmailController.text.trim(),
        'token': _tokenController.text.trim(),
        'password': _passwordController.text,
        'password_confirmation': _confirmPasswordController.text,
      });

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final msg = data['message'] ?? 'Fjalëkalimi u rivendos me sukses!';

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(msg), backgroundColor: Colors.green),
          );
          Navigator.pop(context);
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
          SnackBar(content: Text('Gabim: $e'), backgroundColor: Colors.redAccent),
        );
      }
    } finally {
      if (mounted) setState(() => _resetLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final branding = context.watch<BrandingCubit>().state;

    return Scaffold(
      backgroundColor: theme.colorScheme.surface,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          _showTokenReset ? coreTr(context, 'auth.reset_password_title') : coreTr(context, 'auth.forgot_title'),
          style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 18),
        ),
      ),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(28.0),
          child: Container(
            constraints: const BoxConstraints(maxWidth: 420),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Center(
                  child: Container(
                    height: 72,
                    width: 72,
                    decoration: BoxDecoration(
                      color: branding.primaryColor.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Icon(Icons.lock_reset_rounded, color: branding.primaryColor, size: 38),
                  ),
                ),
                const SizedBox(height: 20),
                Text(
                  _showTokenReset ? coreTr(context, 'auth.reset_password_title') : coreTr(context, 'auth.forgot_title'),
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900, letterSpacing: -0.5),
                ),
                const SizedBox(height: 8),
                Text(
                  _showTokenReset
                      ? coreTr(context, 'auth.reset_password_subtitle')
                      : coreTr(context, 'auth.forgot_subtitle'),
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 13, color: Colors.grey, height: 1.4),
                ),
                const SizedBox(height: 30),

                if (!_showTokenReset) ...[
                  Form(
                    key: _emailFormKey,
                    child: Column(
                      children: [
                        PremiumTextField(
                          controller: _emailController,
                          label: coreTr(context, 'auth.email'),
                          hint: coreTr(context, 'auth.email_hint'),
                          keyboardType: TextInputType.emailAddress,
                          validator: (v) {
                            if (v == null || v.trim().isEmpty) return coreTr(context, 'auth.email_required');
                            if (!v.contains('@')) return 'Shkruani një email të vlefshëm.';
                            return null;
                          },
                        ),
                        const SizedBox(height: 24),
                        PremiumButton(
                          onPressed: _handleSendResetLink,
                          label: _loading ? coreTr(context, 'auth.authenticating') : coreTr(context, 'auth.send_reset_link'),
                          icon: Icons.send_rounded,
                          loading: _loading,
                          expand: true,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),
                  if (_linkSent)
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: Colors.green.shade50,
                        border: Border.all(color: Colors.green.shade200),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Column(
                        children: [
                          const Row(
                            children: [
                              Icon(Icons.check_circle_rounded, color: Colors.green, size: 20),
                              SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  'Emaili u dërgua me sukses! Kontrolloni inboksin tuaj.',
                                  style: TextStyle(color: Colors.green, fontWeight: FontWeight.bold, fontSize: 12),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          TextButton(
                            onPressed: () => setState(() => _showTokenReset = true),
                            child: Text(
                              coreTr(context, 'auth.have_token_prompt'),
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                            ),
                          )
                        ],
                      ),
                    ),
                ] else ...[
                  Form(
                    key: _resetFormKey,
                    child: Column(
                      children: [
                        PremiumTextField(
                          controller: _resetEmailController,
                          label: coreTr(context, 'auth.email'),
                          hint: coreTr(context, 'auth.email_hint'),
                          keyboardType: TextInputType.emailAddress,
                          validator: (v) => (v == null || v.trim().isEmpty) ? coreTr(context, 'auth.email_required') : null,
                        ),
                        const SizedBox(height: 14),
                        PremiumTextField(
                          controller: _tokenController,
                          label: coreTr(context, 'auth.token_label'),
                          hint: 'Kodi nga email-i (token)',
                          validator: (v) => (v == null || v.trim().isEmpty) ? 'Ju lutemi shkruani kodin e rivendosjes.' : null,
                        ),
                        const SizedBox(height: 14),
                        PremiumTextField(
                          controller: _passwordController,
                          label: coreTr(context, 'auth.new_password_label'),
                          hint: 'At least 8 characters',
                          obscureText: _obscurePassword,
                          validator: (v) {
                            if (v == null || v.isEmpty) return coreTr(context, 'auth.password_required');
                            if (v.length < 8) return 'Fjalëkalimi duhet të jetë të paktën 8 karaktere.';
                            return null;
                          },
                          suffixIcon: IconButton(
                            icon: Icon(_obscurePassword ? Icons.visibility_off_outlined : Icons.visibility_outlined, size: 20),
                            onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                          ),
                        ),
                        const SizedBox(height: 14),
                        PremiumTextField(
                          controller: _confirmPasswordController,
                          label: coreTr(context, 'auth.confirm_password_label'),
                          hint: 'Përsëritni fjalëkalimin e ri',
                          obscureText: _obscureConfirmPassword,
                          validator: (v) {
                            if (v == null || v.isEmpty) return 'Konfirmoni fjalëkalimin.';
                            if (v != _passwordController.text) return 'Konfirmimi nuk përputhet me fjalëkalimin.';
                            return null;
                          },
                          suffixIcon: IconButton(
                            icon: Icon(_obscureConfirmPassword ? Icons.visibility_off_outlined : Icons.visibility_outlined, size: 20),
                            onPressed: () => setState(() => _obscureConfirmPassword = !_obscureConfirmPassword),
                          ),
                        ),
                        const SizedBox(height: 24),
                        PremiumButton(
                          onPressed: _handleResetPassword,
                          label: _resetLoading ? coreTr(context, 'auth.authenticating') : coreTr(context, 'auth.reset_password_title'),
                          icon: Icons.check_circle_rounded,
                          loading: _resetLoading,
                          expand: true,
                        ),
                        const SizedBox(height: 12),
                        TextButton(
                          onPressed: () => setState(() => _showTokenReset = false),
                          child: Text(coreTr(context, 'common.back'), style: const TextStyle(fontSize: 12)),
                        )
                      ],
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }

  @override
  void dispose() {
    _emailController.dispose();
    _resetEmailController.dispose();
    _tokenController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }
}
