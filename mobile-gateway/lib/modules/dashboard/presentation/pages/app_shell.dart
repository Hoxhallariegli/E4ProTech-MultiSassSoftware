import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'dashboard_page.dart';
import 'modules_page.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../../auth/presentation/pages/login_page.dart';
import '../../../settings/presentation/pages/settings_page.dart';
import '../../../../l10n/core_localization.dart';
import '../../../../services/auth_service.dart';
import '../../../../core/branding/branding_cubit.dart';
import '../../../../core/widgets/premium_header.dart';
import '../../../../core/widgets/sidebar.dart';
import '../../../../core/widgets/expired_subscription_banner.dart';
import '../../../../core/notifications/push_service.dart';
import '../../booking/presentation/pages/booking_calendar_page.dart';

class AppShell extends StatefulWidget {
  final int initialIndex;
  const AppShell({super.key, this.initialIndex = 0});

  @override
  State<AppShell> createState() => _AppShellState();
}

class _AppShellState extends State<AppShell> {
  late int _currentIndex;
  final GlobalKey<BookingCalendarPageState> _calendarKey = GlobalKey<BookingCalendarPageState>();

  late final List<Widget> _pages = [
    DashboardPage(calendarKey: _calendarKey),
    const ModulesPage(),
    const SettingsPage(),
  ];

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
    _checkAuth();
    AuthService.instance.addListener(_handleAuthChange);
    _handleAuthChange(); // Initial branding load
    PushService.initialize();
  }

  bool _routeArgsApplied = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (!_routeArgsApplied) {
      final args = ModalRoute.of(context)?.settings.arguments;
      if (args is int && args >= 0 && args < _pages.length) {
        _currentIndex = args;
      }
      _routeArgsApplied = true;
    }
  }

  @override
  void dispose() {
    AuthService.instance.removeListener(_handleAuthChange);
    super.dispose();
  }

  void _handleAuthChange() {
    final businessData = AuthService.instance.user?['business'];
    if (businessData != null) {
      context.read<BrandingCubit>().updateBranding(Map<String, dynamic>.from(businessData));
    }
    if (mounted) setState(() {});
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

  String _getPageTitle(BuildContext context, int index) {
    switch (index) {
      case 0: return coreTr(context, 'nav.dashboard') == 'Dashboard' ? 'Agenda & Bookings' : 'Axhenda & Rezervimet';
      case 1: return coreTr(context, 'modules.title');
      case 2: return coreTr(context, 'settings.header');
      default: return 'LaraFlutter';
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      drawer: const Sidebar(),
      appBar: PremiumHeader(
        title: _getPageTitle(context, _currentIndex),
        actions: _currentIndex == 0
            ? [
                IconButton(
                  tooltip: 'Rifresko',
                  icon: const Icon(Icons.refresh_rounded),
                  onPressed: () {
                    _calendarKey.currentState?.refreshAll(reloadBarbers: true);
                  },
                ),
                const SizedBox(width: 8),
              ]
            : null,
        onShopSwitched: () {
          _calendarKey.currentState?.refreshAll(reloadBarbers: true);
          if (mounted) setState(() {});
        },
      ),
      body: Column(
        children: [
          const ExpiredSubscriptionBanner(),
          Expanded(
            child: IndexedStack(
              index: _currentIndex,
              children: _pages,
            ),
          ),
        ],
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
