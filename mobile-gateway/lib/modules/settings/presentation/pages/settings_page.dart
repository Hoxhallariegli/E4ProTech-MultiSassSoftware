import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:mobile_gateway/core/localization/locale_cubit.dart';
import 'package:mobile_gateway/l10n/core_localization.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/core/widgets/metric_card.dart';
import 'package:mobile_gateway/core/realtime/realtime_service.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/modules/auth/presentation/pages/login_page.dart';
import 'package:mobile_gateway/modules/settings/presentation/pages/notification_settings_page.dart';

class SettingsPage extends StatefulWidget {
  const SettingsPage({super.key});

  @override
  State<SettingsPage> createState() => _SettingsPageState();
}

class _SettingsPageState extends State<SettingsPage> {
  final _urlController = TextEditingController();
  String _currentBaseUrl = '';

  String _apiStatus = 'Checking...';
  String _dbStatus = 'Checking...';
  String _cacheStatus = 'Checking...';
  String _reverbStatus = 'OFFLINE';

  @override
  void initState() {
    super.initState();
    _loadCurrentUrl();
    _loadStatus();
    _checkReverb();
  }

  Future<void> _checkReverb() async {
    await RealtimeService.instance.start();
    final client = RealtimeService.instance.client;

    if (client == null) {
      if (mounted) setState(() => _reverbStatus = 'OFFLINE');
      return;
    }

    if (mounted) setState(() => _reverbStatus = client.connectionState.name.toUpperCase());

    client.onConnectionStateChange.listen((state) {
      if (mounted) setState(() => _reverbStatus = state.name.toUpperCase());
    });
  }

  Future<void> _loadStatus() async {
    try {
      final res = await ApiService.get('/status');
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        if (mounted) {
          setState(() {
            _apiStatus = '200 OK';
            _dbStatus = data['database'] == 'online' ? 'PDO Active' : 'Offline';
            _cacheStatus = data['cache'] == 'redis-live' ? 'Redis Live' : 'File Active';
          });
        }
      }
    } catch (e) {
      if (mounted) setState(() => _apiStatus = 'Offline');
    }
  }

  Future<void> _loadCurrentUrl() async {
    final url = await ApiService.serverUrl;
    setState(() {
      _currentBaseUrl = url;
      _urlController.text = url;
    });
  }

  Future<void> _saveUrl() async {
    final newUrl = _urlController.text.trim();
    await ApiService.setCustomBaseUrl(newUrl);
    await _loadCurrentUrl();

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('API Base URL updated to: $_currentBaseUrl'),
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  Future<void> _handleLogout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');

    if (mounted) {
      Navigator.of(context, rootNavigator: true).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const LoginPage()),
        (route) => false,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      backgroundColor: theme.colorScheme.surface,
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(coreTr(context, 'settings.title'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
            Text(coreTr(context, 'settings.subtitle'), style: const TextStyle(fontSize: 11, color: Colors.grey)),
          ],
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          Row(
            children: [
              Icon(Icons.health_and_safety_outlined, color: theme.colorScheme.primary, size: 20),
              const SizedBox(width: 8),
              Text(coreTr(context, 'settings.health'), style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
            ],
          ),
          const SizedBox(height: 12),
          GridView.count(
            crossAxisCount: 2,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            mainAxisSpacing: 12,
            crossAxisSpacing: 12,
            childAspectRatio: 1.45,
            children: [
              MetricCard(
                title: coreTr(context, 'settings.reverb'),
                value: _reverbStatus,
                icon: Icons.bolt,
                color: _reverbStatus == 'CONNECTED' ? Colors.green : (_reverbStatus == 'CONNECTING' ? Colors.amber : Colors.redAccent),
              ),
              MetricCard(
                title: coreTr(context, 'settings.api'),
                value: _apiStatus,
                icon: Icons.cloud_done,
                color: _apiStatus == '200 OK' ? Colors.green : Colors.redAccent,
              ),
              MetricCard(
                title: coreTr(context, 'settings.db'),
                value: _dbStatus,
                icon: Icons.storage_rounded,
                color: _dbStatus == 'PDO Active' ? Colors.blue : Colors.redAccent,
              ),
              MetricCard(
                title: coreTr(context, 'settings.cache'),
                value: _cacheStatus,
                icon: Icons.memory,
                color: Colors.purple,
              ),
            ],
          ),
          const SizedBox(height: 24),
          PremiumCard(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(Icons.dns_outlined, color: theme.colorScheme.primary),
                    const SizedBox(width: 8),
                    Text(coreTr(context, 'settings.endpoint'), style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15)),
                  ],
                ),
                const SizedBox(height: 12),
                Text(
                  coreTr(context, 'settings.current_server'),
                  style: TextStyle(fontSize: 12, color: theme.colorScheme.onSurfaceVariant.withOpacity(0.7)),
                ),
                const SizedBox(height: 4),
                Text(
                  _currentBaseUrl,
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, fontFamily: 'monospace'),
                ),
                const SizedBox(height: 20),
                PremiumTextField(
                  controller: _urlController,
                  label: 'Server API Base URL',
                  hint: 'e.g. http://10.10.12.14:5000',
                ),
                const SizedBox(height: 16),
                PremiumButton(
                  onPressed: _saveUrl,
                  label: coreTr(context, 'settings.save_endpoint'),
                  icon: Icons.save_outlined,
                  expand: true,
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          PremiumCard(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(Icons.notifications_none_rounded, color: theme.colorScheme.primary),
                    const SizedBox(width: 8),
                    Text(coreTr(context, 'settings.notifications'), style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15)),
                  ],
                ),
                const SizedBox(height: 12),
                Text(
                  coreTr(context, 'settings.notifications_desc'),
                  style: const TextStyle(fontSize: 12, color: Colors.grey),
                ),
                const SizedBox(height: 16),
                PremiumButton(
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const NotificationSettingsPage()),
                    );
                  },
                  label: coreTr(context, 'settings.configure_notifications'),
                  icon: Icons.settings_input_component_rounded,
                  expand: true,
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          PremiumCard(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(Icons.language_rounded, color: theme.colorScheme.primary),
                    const SizedBox(width: 8),
                    Text(coreTr(context, 'settings.language'), style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15)),
                  ],
                ),
                const SizedBox(height: 12),
                Text(
                  coreTr(context, 'settings.language_desc'),
                  style: const TextStyle(fontSize: 12, color: Colors.grey),
                ),
                const SizedBox(height: 16),
                BlocBuilder<LocaleCubit, LocaleState>(
                  builder: (context, state) {
                    final currentLocale = state.locale;
                    return Row(
                      children: [
                        Expanded(
                          child: _LanguageOption(
                            label: 'English',
                            code: 'en',
                            isSelected: currentLocale.languageCode == 'en',
                            onTap: () => context.read<LocaleCubit>().setLocale('en'),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: _LanguageOption(
                            label: 'Shqip',
                            code: 'sq',
                            isSelected: currentLocale.languageCode == 'sq',
                            onTap: () => context.read<LocaleCubit>().setLocale('sq'),
                          ),
                        ),
                      ],
                    );
                  },
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          PremiumCard(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(Icons.person_outline, color: Colors.redAccent),
                    const SizedBox(width: 8),
                    Text(coreTr(context, 'settings.account'), style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15)),
                  ],
                ),
                const SizedBox(height: 16),
                PremiumButton(
                  onPressed: _handleLogout,
                  label: coreTr(context, 'settings.logout'),
                  icon: Icons.logout_rounded,
                  expand: true,
                  variant: PremiumButtonVariant.danger,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _urlController.dispose();
    super.dispose();
  }
}

class _LanguageOption extends StatelessWidget {
  final String label;
  final String code;
  final bool isSelected;
  final VoidCallback onTap;

  const _LanguageOption({
    required this.label,
    required this.code,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: isSelected ? theme.colorScheme.primary.withOpacity(0.1) : Colors.transparent,
          border: Border.all(
            color: isSelected ? theme.colorScheme.primary : theme.colorScheme.outlineVariant,
            width: isSelected ? 2 : 1,
          ),
          borderRadius: BorderRadius.circular(16),
        ),
        child: Center(
          child: Text(
            label,
            style: TextStyle(
              fontWeight: isSelected ? FontWeight.w900 : FontWeight.w600,
              color: isSelected ? theme.colorScheme.primary : theme.colorScheme.onSurface,
            ),
          ),
        ),
      ),
    );
  }
}
