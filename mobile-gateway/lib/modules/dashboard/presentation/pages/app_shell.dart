import 'package:flutter/material.dart';
import 'dashboard_page.dart';
import 'modules_page.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../../auth/presentation/pages/login_page.dart';
import '../../../settings/presentation/pages/settings_page.dart';
import '../../../../l10n/core_localization.dart';

class AppShell extends StatefulWidget {
  const AppShell({super.key});

  @override
  State<AppShell> createState() => _AppShellState();
}

class _AppShellState extends State<AppShell> {
  int _currentIndex = 0;

  @override
  void initState() {
    super.initState();
    _checkAuth();
  }

  Future<void> _checkAuth() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');
    if (token == null || token.isEmpty) {
      if (mounted) {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => const LoginPage()),
        );
      }
    }
  }

  final List<Widget> _pages = [
    const DashboardPage(),
    const ModulesPage(),
    const SettingsPage(),
  ];

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: _pages,
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (index) => setState(() => _currentIndex = index),
        backgroundColor: theme.colorScheme.surface,
        selectedItemColor: theme.colorScheme.primary,
        unselectedItemColor: Colors.grey,
        selectedLabelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
        unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.w500, fontSize: 12),
        type: BottomNavigationBarType.fixed,
        items: [
          BottomNavigationBarItem(
            icon: const Icon(Icons.dashboard_customize_rounded),
            activeIcon: const Icon(Icons.dashboard_customize_rounded),
            label: coreTr(context, 'nav.dashboard'),
          ),
          BottomNavigationBarItem(
            icon: const Icon(Icons.layers_outlined),
            activeIcon: const Icon(Icons.layers_rounded),
            label: coreTr(context, 'nav.modules'),
          ),
          BottomNavigationBarItem(
            icon: const Icon(Icons.settings_suggest_outlined),
            activeIcon: const Icon(Icons.settings_suggest_rounded),
            label: coreTr(context, 'nav.settings'),
          ),
        ],
      ),
    );
  }
}
