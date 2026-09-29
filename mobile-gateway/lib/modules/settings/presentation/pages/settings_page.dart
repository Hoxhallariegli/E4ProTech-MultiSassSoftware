import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:image_picker/image_picker.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:mobile_gateway/core/localization/locale_cubit.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';
import 'package:mobile_gateway/core/theme/theme_cubit.dart';
import 'package:mobile_gateway/l10n/core_localization.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/core/widgets/metric_card.dart';
import 'package:mobile_gateway/core/realtime/realtime_service.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';
import 'package:mobile_gateway/modules/auth/presentation/pages/login_page.dart';
import 'package:mobile_gateway/modules/settings/presentation/pages/notification_settings_page.dart';
import 'package:mobile_gateway/modules/dashboard/message_log/presentation/pages/message_log_list_page.dart';

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
    try {
      await ApiService.post('/logout', {});
    } catch (e) {
      debugPrint('Logout error: $e');
    }

    if (mounted) {
      context.read<BrandingCubit>().reset();
    }

    await AuthService.instance.logout();

    if (mounted) {
      Navigator.of(context, rootNavigator: true).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const LoginPage()),
        (route) => false,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isAdmin = AuthService.instance.user?['is_admin'] == true;

    return BlocBuilder<BrandingCubit, BrandingState>(
      builder: (context, branding) {
        final theme = Theme.of(context);
        return Scaffold(
          backgroundColor: theme.colorScheme.surface,
          body: ListView(
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 120),
            children: [
              // 1. Subscription & Business Profile
              _BusinessBanner(branding: branding, onAuthChange: _handleAuthChange),
              const SizedBox(height: 20),

              // 2. Theme Mode Switcher (Dark / Light / System)
              const _ThemeModeSelectorCard(),
              const SizedBox(height: 16),

              // 3. Custom App Theme Color Picker
              _CustomColorPickerCard(branding: branding),
              const SizedBox(height: 16),

              // 4. App Update & APK Download
              const _AppVersionCard(),
              const SizedBox(height: 16),

              // 5. SMS Gateway & Device Management (Combined & Permission-Gated)
              _SmsGatewayGroupCard(branding: branding, onAuthChange: _handleAuthChange),
              const SizedBox(height: 16),

              // 6. Language Settings
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
                    BlocBuilder<LocaleCubit, LocaleState>(
                      builder: (context, state) {
                        final currentLocale = state.locale;
                        return Row(
                          children: [
                            Expanded(
                              child: _LanguageOption(
                                label: 'Albanian',
                                code: 'sq',
                                isSelected: currentLocale.languageCode == 'sq',
                                onTap: () => context.read<LocaleCubit>().setLocale('sq'),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: _LanguageOption(
                                label: 'English',
                                code: 'en',
                                isSelected: currentLocale.languageCode == 'en',
                                onTap: () => context.read<LocaleCubit>().setLocale('en'),
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

              // 7. App Connectivity & Config (ADMIN ONLY)
              if (isAdmin) ...[
                _CompactEndpointCard(
                  currentUrl: _currentBaseUrl,
                  controller: _urlController,
                  onSave: _saveUrl,
                ),
                const SizedBox(height: 16),
              ],

              // 9. Navigation Shortcuts
              _SimpleNavCard(
                label: coreTr(context, 'settings.notifications'),
                icon: Icons.notifications_none_rounded,
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const NotificationSettingsPage())),
              ),
              const SizedBox(height: 16),

              // 10. Security
              _SimpleNavCard(
                label: coreTr(context, 'settings.logout'),
                icon: Icons.logout_rounded,
                onTap: _handleLogout,
              ),
            ],
          ),
        );
      },
    );
  }

  void _handleAuthChange() {
    if (mounted) setState(() {});
  }

  @override
  void dispose() {
    _urlController.dispose();
    super.dispose();
  }
}

class _AppVersionCard extends StatefulWidget {
  const _AppVersionCard();

  @override
  State<_AppVersionCard> createState() => _AppVersionCardState();
}

class _AppVersionCardState extends State<_AppVersionCard> {
  bool _loading = false;
  Map<String, dynamic>? _versionInfo;

  @override
  void initState() {
    super.initState();
    _checkVersion();
  }

  Future<void> _checkVersion() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService.get('/app-version');
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        if (mounted) {
          setState(() {
            _versionInfo = data;
            _loading = false;
          });
        }
      } else {
        if (mounted) setState(() => _loading = false);
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _downloadApk() async {
    final baseUrl = await ApiService.serverUrl;
    final downloadUrl = Uri.parse('$baseUrl/download/apk');

    try {
      if (await canLaunchUrl(downloadUrl)) {
        await launchUrl(downloadUrl, mode: LaunchMode.externalApplication);
      } else {
        await launchUrl(downloadUrl, mode: LaunchMode.platformDefault);
      }
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final hasApk = _versionInfo?['has_apk'] == true;
    final version = _versionInfo?['latest_version'] ?? '1.0.2';
    final sizeMb = _versionInfo?['file_size_mb'] ?? '56.4';
    const currentInstalledVersion = '1.0.2';
    final isNewVersionAvailable = hasApk && (version != currentInstalledVersion);

    return PremiumCard(
      padding: const EdgeInsets.all(18),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.blue.withOpacity(0.15),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(Icons.system_update_rounded, color: Colors.blue, size: 22),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Përditësimi i Aplikacionit (APK)',
                      style: TextStyle(fontWeight: FontWeight.w900, fontSize: 15, color: isDark ? Colors.white : null),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Versioni Aktual: v$version ($sizeMb MB)',
                      style: const TextStyle(fontSize: 11, color: Colors.grey),
                    ),
                  ],
                ),
              ),
              if (_loading)
                const SizedBox(width: 20, height: 20, child: CircularProgressIndicator.adaptive(strokeWidth: 2))
              else
                IconButton(
                  tooltip: 'Rifresko Versionin',
                  icon: const Icon(Icons.refresh_rounded, size: 20),
                  onPressed: _checkVersion,
                ),
            ],
          ),
          if (isNewVersionAvailable) ...[
            const SizedBox(height: 14),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.green.withOpacity(0.12),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: Colors.green.withOpacity(0.3)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.verified_rounded, color: Colors.green, size: 20),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '⚡ Version i Ri Gati për Shkarkim (v$version)',
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5, color: Colors.green),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          _versionInfo?['release_notes'] ?? 'Shkarkoni skedarin APK direkt nga serveri.',
                          style: TextStyle(fontSize: 10.5, color: isDark ? Colors.white70 : Colors.black87),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _downloadApk,
                icon: const Icon(Icons.download_rounded, size: 18),
                label: const Text('Shkarko & Instalo APK-në', style: TextStyle(fontWeight: FontWeight.bold)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.blue.shade700,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
              ),
            ),
          ] else ...[
            const SizedBox(height: 10),
            Row(
              children: [
                const Icon(Icons.check_circle_rounded, color: Colors.green, size: 16),
                const SizedBox(width: 6),
                Text(
                  'Aplikacioni është i përditësuar (v$currentInstalledVersion)',
                  style: const TextStyle(fontSize: 11.5, color: Colors.green, fontWeight: FontWeight.bold),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }
}

class _ThemeModeSelectorCard extends StatelessWidget {
  const _ThemeModeSelectorCard();

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return BlocBuilder<ThemeCubit, ThemeMode>(
      builder: (context, currentMode) {
        return PremiumCard(
          padding: const EdgeInsets.all(18),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: theme.colorScheme.primary.withOpacity(0.15),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: Icon(
                      currentMode == ThemeMode.dark
                          ? Icons.dark_mode_rounded
                          : (currentMode == ThemeMode.light ? Icons.wb_sunny_rounded : Icons.brightness_auto_rounded),
                      color: theme.colorScheme.primary,
                      size: 22,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          coreTr(context, 'settings.theme_mode'),
                          style: TextStyle(fontWeight: FontWeight.w900, fontSize: 15, color: isDark ? Colors.white : null),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          coreTr(context, 'settings.theme_mode_desc'),
                          style: const TextStyle(fontSize: 11, color: Colors.grey),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: _ThemeOptionButton(
                      label: coreTr(context, 'settings.theme_light'),
                      icon: Icons.wb_sunny_rounded,
                      isSelected: currentMode == ThemeMode.light,
                      onTap: () => context.read<ThemeCubit>().setThemeMode(ThemeMode.light),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: _ThemeOptionButton(
                      label: coreTr(context, 'settings.theme_dark'),
                      icon: Icons.dark_mode_rounded,
                      isSelected: currentMode == ThemeMode.dark,
                      onTap: () => context.read<ThemeCubit>().setThemeMode(ThemeMode.dark),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: _ThemeOptionButton(
                      label: coreTr(context, 'settings.theme_system'),
                      icon: Icons.brightness_auto_rounded,
                      isSelected: currentMode == ThemeMode.system,
                      onTap: () => context.read<ThemeCubit>().setThemeMode(ThemeMode.system),
                    ),
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }
}

class _ThemeOptionButton extends StatelessWidget {
  final String label;
  final IconData icon;
  final bool isSelected;
  final VoidCallback onTap;

  const _ThemeOptionButton({
    required this.label,
    required this.icon,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
        decoration: BoxDecoration(
          color: isSelected
              ? theme.colorScheme.primary
              : (isDark ? const Color(0xFF1E212B) : theme.colorScheme.surfaceContainerHighest.withOpacity(0.5)),
          border: Border.all(
            color: isSelected ? theme.colorScheme.primary : (isDark ? const Color(0xFF3B4052) : theme.colorScheme.outlineVariant),
            width: isSelected ? 2 : 1,
          ),
          borderRadius: BorderRadius.circular(16),
        ),
        child: Column(
          children: [
            Icon(
              icon,
              size: 20,
              color: isSelected ? Colors.white : theme.colorScheme.primary,
            ),
            const SizedBox(height: 6),
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                fontWeight: isSelected ? FontWeight.w900 : FontWeight.w700,
                color: isSelected ? Colors.white : (isDark ? Colors.white : Colors.black87),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _BusinessBanner extends StatelessWidget {
  final BrandingState branding;
  final VoidCallback onAuthChange;

  const _BusinessBanner({
    required this.branding,
    required this.onAuthChange,
  });

  Future<void> _openEditModal(BuildContext context) async {
    final nameController = TextEditingController(text: branding.appName);
    String? selectedImagePath;
    bool saving = false;

    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) => StatefulBuilder(
        builder: (context, setSheetState) => Padding(
          padding: EdgeInsets.fromLTRB(22, 16, 22, MediaQuery.of(context).viewInsets.bottom + 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(child: Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10)))),
              const SizedBox(height: 18),
              Text(coreTr(sheetContext, 'settings.edit_profile_logo'), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900)),
              const SizedBox(height: 16),
              Center(
                child: InkWell(
                  onTap: () async {
                    final picker = ImagePicker();
                    final picked = await picker.pickImage(source: ImageSource.gallery);
                    if (picked != null) {
                      setSheetState(() => selectedImagePath = picked.path);
                    }
                  },
                  borderRadius: BorderRadius.circular(24),
                  child: Container(
                    width: 90,
                    height: 90,
                    decoration: BoxDecoration(
                      color: Theme.of(context).colorScheme.primaryContainer.withOpacity(0.5),
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(color: Theme.of(context).colorScheme.outlineVariant),
                    ),
                    child: selectedImagePath != null
                        ? ClipRRect(
                            borderRadius: BorderRadius.circular(24),
                            child: Image.file(File(selectedImagePath!), fit: BoxFit.cover),
                          )
                        : (branding.logoUrl != null
                            ? ClipRRect(
                                borderRadius: BorderRadius.circular(24),
                                child: Image.network(branding.logoUrl!, fit: BoxFit.cover),
                              )
                            : const Icon(Icons.add_a_photo_rounded, size: 32)),
                  ),
                ),
              ),
              Center(child: Padding(padding: const EdgeInsets.only(top: 8, bottom: 16), child: Text(coreTr(sheetContext, 'settings.click_to_choose_logo'), style: const TextStyle(fontSize: 11, color: Colors.grey)))),
              TextFormField(
                controller: nameController,
                decoration: InputDecoration(labelText: coreTr(sheetContext, 'settings.app_name_label'), border: const OutlineInputBorder()),
              ),
              const SizedBox(height: 18),
              SizedBox(
                width: double.infinity,
                child: FilledButton.icon(
                  onPressed: saving ? null : () async {
                    setSheetState(() => saving = true);
                    try {
                      final body = <String, dynamic>{
                        'app_name': nameController.text.trim(),
                      };
                      final files = <String, String>{};
                      if (selectedImagePath != null) {
                        files['logo'] = selectedImagePath!;
                      }

                      final res = await ApiService.postMultipart('/business/update-branding', body, files: files);
                      if (res.statusCode == 200) {
                        final resData = jsonDecode(res.body);
                        if (resData['business'] != null) {
                          context.read<BrandingCubit>().updateBranding(Map<String, dynamic>.from(resData['business']));
                        }
                        await AuthService.instance.sync();
                        onAuthChange();
                        if (sheetContext.mounted) Navigator.pop(sheetContext);
                      }
                    } catch (e) {
                      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gabim: $e')));
                    } finally {
                      setSheetState(() => saving = false);
                    }
                  },
                  icon: saving ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : const Icon(Icons.check_rounded),
                  label: Text(saving ? coreTr(sheetContext, 'settings.saving') : coreTr(sheetContext, 'settings.save_changes'), style: const TextStyle(fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final canEdit = AuthService.instance.hasPermission('edit_barber_shops') || AuthService.instance.user?['is_admin'] == true;

    return InkWell(
      onTap: canEdit ? () => _openEditModal(context) : null,
      borderRadius: BorderRadius.circular(28),
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [branding.primaryColor, branding.primaryColor.withOpacity(0.7)],
          ),
          borderRadius: BorderRadius.circular(28),
          boxShadow: [
            BoxShadow(color: branding.primaryColor.withOpacity(0.3), blurRadius: 15, offset: const Offset(0, 8)),
          ],
        ),
        child: Row(
          children: [
            Container(
              width: 54,
              height: 54,
              decoration: BoxDecoration(
                color: Colors.white24,
                borderRadius: BorderRadius.circular(16),
              ),
              child: branding.logoUrl != null
                  ? ClipRRect(
                      borderRadius: BorderRadius.circular(16),
                      child: Image.network(branding.logoUrl!, fit: BoxFit.cover),
                    )
                  : const Icon(Icons.business_center_rounded, color: Colors.white, size: 28),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    branding.appName,
                    style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w900),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    '${branding.planName} • ${branding.trialDaysLeft > 0 ? coreTr(context, 'settings.days_remaining', {'days': branding.trialDaysLeft.toString()}) : coreTr(context, 'settings.plan_expired')}',
                    style: const TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),
            if (canEdit)
              Container(
                padding: const EdgeInsets.all(6),
                decoration: const BoxDecoration(color: Colors.white24, shape: BoxShape.circle),
                child: const Icon(Icons.edit_rounded, color: Colors.white, size: 16),
              )
            else
              const Icon(Icons.verified_rounded, color: Colors.white, size: 20),
          ],
        ),
      ),
    );
  }
}

class _CompactHealthRow extends StatelessWidget {
  final String reverb, api, db, cache;
  const _CompactHealthRow({required this.reverb, required this.api, required this.db, required this.cache});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: [
          _HealthChip(label: 'Reverb', status: reverb, icon: Icons.bolt),
          _HealthChip(label: 'API', status: api, icon: Icons.cloud_queue),
          _HealthChip(label: 'DB', status: db, icon: Icons.storage),
          _HealthChip(label: 'Cache', status: cache, icon: Icons.memory),
        ],
      ),
    );
  }
}

class _HealthChip extends StatelessWidget {
  final String label, status;
  final IconData icon;
  const _HealthChip({required this.label, required this.status, required this.icon});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isOk = status.contains('OK') || status.contains('CONNECTED') || status.contains('Active');
    final color = isOk ? Colors.green : (status.contains('Checking') ? Colors.orange : Colors.red);

    return Container(
      margin: const EdgeInsets.only(right: 8),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: theme.colorScheme.surface,
        border: Border.all(color: theme.colorScheme.outlineVariant),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: color),
          const SizedBox(width: 6),
          Text(label, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700)),
        ],
      ),
    );
  }
}

class _CompactEndpointCard extends StatefulWidget {
  final String currentUrl;
  final TextEditingController controller;
  final VoidCallback onSave;
  const _CompactEndpointCard({required this.currentUrl, required this.controller, required this.onSave});

  @override
  State<_CompactEndpointCard> createState() => _CompactEndpointCardState();
}

class _CompactEndpointCardState extends State<_CompactEndpointCard> {
  bool _isEditing = false;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return PremiumCard(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('API NODE', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w900, color: Colors.grey, letterSpacing: 1)),
              GestureDetector(
                onTap: () => setState(() => _isEditing = !_isEditing),
                child: Text(_isEditing ? 'CANCEL' : 'CHANGE', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w900, color: theme.colorScheme.primary)),
              ),
            ],
          ),
          const SizedBox(height: 8),
          if (!_isEditing)
            Text(widget.currentUrl, style: const TextStyle(fontFamily: 'monospace', fontWeight: FontWeight.bold, fontSize: 13))
          else
            Row(
              children: [
                Expanded(child: TextField(controller: widget.controller, style: const TextStyle(fontSize: 13), decoration: const InputDecoration(isDense: true, border: InputBorder.none))),
                IconButton(onPressed: () { widget.onSave(); setState(() => _isEditing = false); }, icon: const Icon(Icons.check_circle_outline_rounded, size: 20)),
              ],
            ),
        ],
      ),
    );
  }
}

class _SimpleNavCard extends StatelessWidget {
  final String label;
  final IconData icon;
  final VoidCallback onTap;
  const _SimpleNavCard({required this.label, required this.icon, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return PremiumCard(
      padding: EdgeInsets.zero,
      child: ListTile(
        onTap: onTap,
        leading: Icon(icon, size: 20, color: theme.colorScheme.onSurfaceVariant),
        title: Text(label, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700)),
        trailing: const Icon(Icons.arrow_forward_ios_rounded, size: 12, color: Colors.grey),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      ),
    );
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

class _CustomColorPickerCard extends StatefulWidget {
  final BrandingState branding;
  const _CustomColorPickerCard({required this.branding});

  @override
  State<_CustomColorPickerCard> createState() => _CustomColorPickerCardState();
}

class _CustomColorPickerCardState extends State<_CustomColorPickerCard> {
  late TextEditingController _hexController;
  bool _saving = false;

  final List<({String name, String hex})> _colorPresets = [
    (name: 'Electric Blue', hex: '#2563EB'),
    (name: 'Luxury Gold', hex: '#D97706'),
    (name: 'Emerald Green', hex: '#059669'),
    (name: 'Royal Purple', hex: '#7C3AED'),
    (name: 'Crimson Red', hex: '#DC2626'),
    (name: 'Rose Pink', hex: '#DB2777'),
    (name: 'Midnight Slate', hex: '#334155'),
  ];

  @override
  void initState() {
    super.initState();
    final currentHex = "#${widget.branding.primaryColor.value.toRadixString(16).substring(2).toUpperCase()}";
    _hexController = TextEditingController(text: currentHex);
  }

  Future<void> _applyColor(String hex) async {
    final cleanHex = hex.trim().toUpperCase().replaceAll('0XFF', '#').replaceAll('0X', '#');
    final formattedHex = cleanHex.startsWith('#') ? cleanHex : '#$cleanHex';

    if (!RegExp(r'^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$').hasMatch(formattedHex)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Vendosni një kod heksadecimal të vlefshëm (p.sh. #2563EB)'), behavior: SnackBarBehavior.floating),
      );
      return;
    }

    setState(() => _saving = true);
    try {
      final res = await ApiService.post('/business/update-branding', {
        'primary_color': formattedHex,
      });

      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['business'] != null) {
          context.read<BrandingCubit>().updateBranding(Map<String, dynamic>.from(body['business']));
        }
        if (mounted) {
          _hexController.text = formattedHex;
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Ngjyra e temës u ruajt me sukses ($formattedHex)!'),
              backgroundColor: Colors.green,
              behavior: SnackBarBehavior.floating,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gabim gjatë ruajtjes: $e'), backgroundColor: Colors.red, behavior: SnackBarBehavior.floating),
        );
      }
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final currentHex = "#${widget.branding.primaryColor.value.toRadixString(16).substring(2).toUpperCase()}";

    return PremiumCard(
      padding: const EdgeInsets.all(18),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: widget.branding.primaryColor.withOpacity(0.15),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(Icons.palette_rounded, color: widget.branding.primaryColor, size: 22),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(coreTr(context, 'settings.brand_color'), style: TextStyle(fontWeight: FontWeight.w900, fontSize: 15, color: isDark ? Colors.white : null)),
                    const SizedBox(height: 2),
                    Text(coreTr(context, 'settings.brand_color_desc', {'hex': currentHex}), style: const TextStyle(fontSize: 11, color: Colors.grey)),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(coreTr(context, 'settings.select_preset_color'), style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: isDark ? const Color(0xFFCBD5E1) : Colors.black87)),
          const SizedBox(height: 12),
          Wrap(
            spacing: 10,
            runSpacing: 10,
            children: _colorPresets.map((preset) {
              final colorValue = Color(int.parse(preset.hex.replaceFirst('#', '0xFF')));
              final isSelected = currentHex.toUpperCase() == preset.hex.toUpperCase();

              return InkWell(
                onTap: () => _applyColor(preset.hex),
                borderRadius: BorderRadius.circular(24),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(
                    color: colorValue.withOpacity(0.18),
                    border: Border.all(
                      color: isSelected ? colorValue : colorValue.withOpacity(0.4),
                      width: isSelected ? 2.5 : 1,
                    ),
                    borderRadius: BorderRadius.circular(24),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 18,
                        height: 18,
                        decoration: BoxDecoration(
                          color: colorValue,
                          shape: BoxShape.circle,
                          border: Border.all(color: Colors.white, width: 1.5),
                        ),
                        child: isSelected ? const Icon(Icons.check, size: 12, color: Colors.white) : null,
                      ),
                      const SizedBox(width: 8),
                      Text(
                        preset.name,
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: isSelected ? FontWeight.w900 : FontWeight.w600,
                          color: isDark ? Colors.white : Colors.black87,
                        ),
                      ),
                    ],
                  ),
                ),
              );
            }).toList(),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 2),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF1E212B) : theme.colorScheme.surfaceContainerHighest,
                    border: Border.all(color: isDark ? const Color(0xFF3B4052) : theme.colorScheme.outlineVariant),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: TextField(
                    controller: _hexController,
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, fontFamily: 'monospace'),
                    decoration: const InputDecoration(
                      hintText: '#2563EB',
                      border: InputBorder.none,
                      isDense: true,
                      prefixIcon: Icon(Icons.colorize_rounded, size: 18),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              ElevatedButton.icon(
                onPressed: _saving ? null : () => _applyColor(_hexController.text),
                icon: _saving ? const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.save_rounded, size: 18),
                label: Text(coreTr(context, 'settings.save'), style: const TextStyle(fontWeight: FontWeight.bold)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: widget.branding.primaryColor,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _hexController.dispose();
    super.dispose();
  }
}

class _SmsGatewayGroupCard extends StatefulWidget {
  final BrandingState branding;
  final VoidCallback onAuthChange;

  const _SmsGatewayGroupCard({
    required this.branding,
    required this.onAuthChange,
  });

  @override
  State<_SmsGatewayGroupCard> createState() => _SmsGatewayGroupCardState();
}

class _SmsGatewayGroupCardState extends State<_SmsGatewayGroupCard> {
  bool _isGatewayDevice = false;
  bool _deviceLoading = false;
  bool _shopSmsLoading = false;

  @override
  void initState() {
    super.initState();
    _checkDeviceStatus();
  }

  Future<void> _checkDeviceStatus() async {
    try {
      final res = await ApiService.get('/device-tokens');
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        final items = body['data'] as List?;
        if (items != null) {
          final user = AuthService.instance.user;
          final shopId = user?['barber_shop_id']?.toString();

          final activeGatewayItem = items.firstWhere(
            (e) => (e['barber_shop_id']?.toString() == shopId) &&
                   (e['is_sms_gateway'] == true || e['is_sms_gateway'] == 1 || e['is_sms_gateway'] == '1'),
            orElse: () => null,
          );

          final isPrimaryForThisShop = activeGatewayItem != null;
          final prefs = await SharedPreferences.getInstance();
          await prefs.setBool('is_sms_gateway_device', isPrimaryForThisShop);

          if (mounted) {
            setState(() => _isGatewayDevice = isPrimaryForThisShop);
            return;
          }
        }
      }
    } catch (_) {}

    final prefs = await SharedPreferences.getInstance();
    final isGateway = prefs.getBool('is_sms_gateway_device') ?? false;
    if (mounted) setState(() => _isGatewayDevice = isGateway);
  }

  Future<String> _getPersistentDeviceId() async {
    String? token;
    try {
      token = await FirebaseMessaging.instance.getToken();
    } catch (_) {}

    if (token == null || token.isEmpty) {
      final prefs = await SharedPreferences.getInstance();
      token = prefs.getString('persistent_device_id');
      if (token == null || token.isEmpty) {
        token = 'device-id-${DateTime.now().millisecondsSinceEpoch}';
        await prefs.setString('persistent_device_id', token);
      }
    }
    return token;
  }

  Future<void> _toggleShopSms(bool enabled) async {
    setState(() => _shopSmsLoading = true);
    try {
      final res = await ApiService.post('/business/toggle-sms', {'enabled': enabled});
      if (res.statusCode == 200) {
        await AuthService.instance.sync();
        widget.onAuthChange();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(enabled ? coreTr(context, 'settings.sms_active') : coreTr(context, 'settings.sms_inactive')),
              behavior: SnackBarBehavior.floating,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gabim: $e'), behavior: SnackBarBehavior.floating),
        );
      }
    } finally {
      if (mounted) setState(() => _shopSmsLoading = false);
    }
  }

  Future<void> _toggleGatewayDevice(bool value) async {
    if (!widget.branding.smsEnabled) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(coreTr(context, 'settings.enable_shop_sms_first')),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    setState(() => _deviceLoading = true);
    try {
      if (value) {
        try {
          NotificationSettings settings = await FirebaseMessaging.instance.requestPermission(
            alert: true,
            badge: true,
            sound: true,
            provisional: false,
          );
          debugPrint('FCM Notification permission status: ${settings.authorizationStatus}');
        } catch (e) {
          debugPrint('FCM permission error: $e');
        }

        try {
          await FirebaseMessaging.instance.subscribeToTopic('all');
        } catch (e) {
          debugPrint('FCM topic subscription error: $e');
        }
      }

      final token = await _getPersistentDeviceId();

      final res = await ApiService.post('/device-tokens/set-primary-gateway', {
        'fcm_token': token,
        'is_sms_gateway': value,
        'device_name': 'Android Phone (${AuthService.instance.user?['name'] ?? 'Staff'})',
      });

      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        final prefs = await SharedPreferences.getInstance();
        await prefs.setBool('is_sms_gateway_device', value);
        if (mounted) {
          setState(() {
            _isGatewayDevice = value;
            _deviceLoading = false;
          });
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(data['message'] ?? (value ? 'SMS Gateway & Firebase Push u aktivizuan.' : 'SMS Gateway u çaktivizua.')),
              behavior: SnackBarBehavior.floating,
            ),
          );
        }
      } else {
        if (mounted) setState(() => _deviceLoading = false);
      }
    } catch (e) {
      if (mounted) {
        setState(() => _deviceLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gabim gjatë ruajtjes: $e'), behavior: SnackBarBehavior.floating),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final canManageShopSms = AuthService.instance.hasPermission('edit_barber_shops') || AuthService.instance.user?['is_admin'] == true;

    return PremiumCard(
      padding: const EdgeInsets.all(18),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: widget.branding.smsEnabled
                      ? Colors.green.withOpacity(0.15)
                      : theme.colorScheme.primary.withOpacity(0.15),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(
                  widget.branding.smsEnabled ? Icons.sms_rounded : Icons.sms_outlined,
                  color: widget.branding.smsEnabled ? Colors.green : theme.colorScheme.primary,
                  size: 22,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      coreTr(context, 'settings.sms_title'),
                      style: TextStyle(fontWeight: FontWeight.w900, fontSize: 15, color: isDark ? Colors.white : null),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      widget.branding.smsEnabled ? coreTr(context, 'settings.sms_active') : coreTr(context, 'settings.sms_inactive'),
                      style: TextStyle(fontSize: 11, color: widget.branding.smsEnabled ? Colors.green : Colors.grey),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          if (canManageShopSms) ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF1E212B) : theme.colorScheme.surfaceContainerHighest.withOpacity(0.4),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: isDark ? const Color(0xFF3B4052) : theme.colorScheme.outlineVariant,
                ),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          coreTr(context, 'settings.sms_shop_enable'),
                          style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          coreTr(context, 'settings.sms_shop_enable_desc'),
                          style: const TextStyle(fontSize: 10.5, color: Colors.grey),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  _shopSmsLoading
                      ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator.adaptive(strokeWidth: 2))
                      : Switch.adaptive(
                          value: widget.branding.smsEnabled,
                          onChanged: _toggleShopSms,
                          activeColor: theme.colorScheme.primary,
                        ),
                ],
              ),
            ),
            const SizedBox(height: 12),
          ],

          AnimatedOpacity(
            duration: const Duration(milliseconds: 200),
            opacity: widget.branding.smsEnabled ? 1.0 : 0.5,
            child: Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: _isGatewayDevice
                    ? Colors.green.withOpacity(0.12)
                    : (isDark ? const Color(0xFF1E212B) : theme.colorScheme.surfaceContainerHighest.withOpacity(0.4)),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: _isGatewayDevice
                      ? Colors.green.withOpacity(0.4)
                      : (isDark ? const Color(0xFF3B4052) : theme.colorScheme.outlineVariant),
                ),
              ),
              child: Row(
                children: [
                  Icon(
                    _isGatewayDevice ? Icons.phonelink_ring_rounded : Icons.phonelink_setup_rounded,
                    size: 20,
                    color: _isGatewayDevice ? Colors.green : Colors.grey,
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          coreTr(context, 'settings.sms_device_enable'),
                          style: TextStyle(
                            fontWeight: FontWeight.w800,
                            fontSize: 13,
                            color: _isGatewayDevice ? Colors.green : (isDark ? Colors.white : Colors.black87),
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          _isGatewayDevice
                              ? coreTr(context, 'settings.sms_device_active_desc')
                              : coreTr(context, 'settings.sms_device_inactive_desc'),
                          style: TextStyle(fontSize: 10.5, color: _isGatewayDevice ? Colors.green : Colors.grey),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  _deviceLoading
                      ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator.adaptive(strokeWidth: 2))
                      : Switch.adaptive(
                          value: _isGatewayDevice && widget.branding.smsEnabled,
                          onChanged: widget.branding.smsEnabled ? _toggleGatewayDevice : null,
                          activeColor: Colors.green,
                        ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const MessageLogListPage()),
                );
              },
              icon: const Icon(Icons.mark_email_read_rounded, size: 18),
              label: Text(
                '📜 Shiko Logjet e Mesazheve',
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
              style: OutlinedButton.styleFrom(
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                padding: const EdgeInsets.symmetric(vertical: 12),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
