import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mobile_gateway/services/auth_service.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/core/widgets/app_scaffold.dart';
import 'package:mobile_gateway/modules/dashboard/presentation/widgets/shop_switcher_widget.dart';
import 'package:mobile_gateway/core/realtime/realtime_service.dart';
import '../cubit/subscription_renewal_cubit.dart';
import '../cubit/subscription_renewal_state.dart';
import '../widgets/subscription_renewal_card.dart';
import '../../data/subscription_renewal_repository.dart';
import 'subscription_renewal_form_page.dart';

class SubscriptionRenewalListPage extends StatelessWidget {
  const SubscriptionRenewalListPage({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (_) => SubscriptionRenewalCubit(SubscriptionRenewalRepository())..load(),
      child: const _SubscriptionRenewalListView(),
    );
  }
}

class _SubscriptionRenewalListView extends StatefulWidget {
  const _SubscriptionRenewalListView();
  @override State<_SubscriptionRenewalListView> createState() => _SubscriptionRenewalListViewState();
}

class _SubscriptionRenewalListViewState extends State<_SubscriptionRenewalListView> {
  final _search = TextEditingController();
  final _scroll = ScrollController();
  final _filters = <String, dynamic>{};
  final repository = SubscriptionRenewalRepository();

  List<Map<String, dynamic>> _planIdFilterOptions = [];
  int _selectedDurationMonths = 6; // 6, 12, 24

  @override
  void initState() {
    super.initState();
    _loadFilterOptions();
    _scroll.addListener(() {
      if (_scroll.position.pixels >= _scroll.position.maxScrollExtent - 320) {
        context.read<SubscriptionRenewalCubit>().loadMore();
      }
    });
    RealtimeService.instance.subscribe('subscription-renewals', (action, data) {
      if (!mounted) return;
      context.read<SubscriptionRenewalCubit>().handleRealtime(action, data);
    }, tenantId: AuthService.instance.user?['barber_shop_id']);
  }

  Future<void> _loadFilterOptions() async {
    final raw = await repository.lookup('plans');
    // Filter out Trial plans
    _planIdFilterOptions = raw.where((p) {
      final name = (p['name'] ?? p['title'] ?? '').toString().toLowerCase();
      return !name.contains('trial') && !name.contains('prova');
    }).toList();

    if (mounted) setState(() {});
  }

  @override
  void dispose() {
    _search.dispose();
    _scroll.dispose();
    super.dispose();
  }

  Future<void> _openForm([Map<String, dynamic>? item, Map<String, dynamic>? selectedPlan, double? calculatedAmount]) async {
    Map<String, dynamic>? prefillItem = item;
    if (prefillItem == null && selectedPlan != null) {
      prefillItem = {
        'plan_id': selectedPlan['id'],
        'amount': calculatedAmount ?? (double.tryParse(selectedPlan['price']?.toString() ?? '0') ?? 0.0),
        'payment_method': 'bank_transfer',
        'notes': 'Kërkesë për renovim $_selectedDurationMonths Muaj.',
      };
    }

    final changed = await Navigator.push<bool>(context, MaterialPageRoute(builder: (_) => SubscriptionRenewalFormPage(item: prefillItem)));
    if (changed == true && mounted) context.read<SubscriptionRenewalCubit>().load(refresh: true);
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final primaryColor = theme.colorScheme.primary;

    final canAdd = AuthService.instance.hasPermission('add_subscription_renewals');
    final canEdit = AuthService.instance.hasPermission('edit_subscription_renewals');
    final canDelete = AuthService.instance.hasPermission('delete_subscription_renewals');

    return AppScaffold(
      currentNavIndex: 1,
      title: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        const Text('Renovimi i Abonimit', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900)),
        InkWell(
          onTap: () {
            showModalBottomSheet(
              context: context,
              isScrollControlled: true,
              backgroundColor: Colors.transparent,
              builder: (_) => ShopSwitcherWidget(onSwitched: () => context.read<SubscriptionRenewalCubit>().refresh()),
            );
          },
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(AuthService.instance.user?['business']?['name'] ?? 'Salloni Aktiv', style: TextStyle(fontSize: 10, color: primaryColor, fontWeight: FontWeight.bold)),
              Icon(Icons.keyboard_arrow_down_rounded, size: 12, color: primaryColor),
            ],
          ),
        ),
      ]),
      actions: [
        IconButton(tooltip: 'Rifresko', onPressed: () => context.read<SubscriptionRenewalCubit>().refresh(), icon: const Icon(Icons.refresh_rounded)),
        const SizedBox(width: 8),
      ],
      floatingActionButton: canAdd ? FloatingActionButton.extended(onPressed: () => _openForm(), icon: const Icon(Icons.add_rounded), label: const Text('Kërkesë e Re')) : null,
      body: Column(
        children: [
          Expanded(
            child: ListView(
              controller: _scroll,
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 120),
              children: [
                // 🌟 HEADER TITLE & DURATION TOGGLE SWITCHER
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: isDark ? primaryColor.withOpacity(0.1) : primaryColor.withOpacity(0.05),
                    borderRadius: BorderRadius.circular(22),
                    border: Border.all(color: primaryColor.withOpacity(0.2)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Zgjidh Planin & Periudhën 🚀', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900)),
                      const SizedBox(height: 2),
                      Text('Shtyni skadimin e abonimit duke zgjedhur planin e dëshiruar:', style: TextStyle(fontSize: 11.5, color: isDark ? Colors.white70 : Colors.black87)),
                      const SizedBox(height: 12),

                      // Duration Selector
                      Container(
                        padding: const EdgeInsets.all(4),
                        decoration: BoxDecoration(
                          color: isDark ? Colors.black38 : Colors.grey.shade200,
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: Row(
                          children: [
                            _DurationTab(
                              label: '6 Muaj',
                              selected: _selectedDurationMonths == 6,
                              onTap: () => setState(() => _selectedDurationMonths = 6),
                            ),
                            _DurationTab(
                              label: '1 Vit (-5%)',
                              selected: _selectedDurationMonths == 12,
                              badge: '5% OFF',
                              badgeColor: const Color(0xFF10B981),
                              onTap: () => setState(() => _selectedDurationMonths = 12),
                            ),
                            _DurationTab(
                              label: '2 Vite (-10%)',
                              selected: _selectedDurationMonths == 24,
                              badge: '10% OFF',
                              badgeColor: Colors.amber.shade800,
                              onTap: () => setState(() => _selectedDurationMonths = 24),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 14),

                // 💎 HORIZONTAL PRICING CARDS LIST
                if (_planIdFilterOptions.isNotEmpty)
                  SizedBox(
                    height: 275,
                    child: ListView.separated(
                      scrollDirection: Axis.horizontal,
                      itemCount: _planIdFilterOptions.length,
                      separatorBuilder: (_, __) => const SizedBox(width: 12),
                      itemBuilder: (context, index) {
                        final plan = _planIdFilterOptions[index];
                        final planName = (plan['name'] ?? 'Plan').toString();
                        final basePrice = double.tryParse(plan['price']?.toString() ?? '0') ?? 0.0;

                        double discountPct = _selectedDurationMonths == 12 ? 0.05 : (_selectedDurationMonths == 24 ? 0.10 : 0.0);
                        double totalPrice = (basePrice * _selectedDurationMonths) * (1.0 - discountPct);
                        double monthlyAvg = totalPrice / _selectedDurationMonths;

                        final isPopular = planName.toLowerCase() == 'pro' || (_planIdFilterOptions.length > 1 && index == 1 && !planName.toLowerCase().contains('unlimited'));

                        return Container(
                          width: 250,
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: isDark ? (isPopular ? primaryColor.withOpacity(0.15) : theme.colorScheme.surfaceContainerHighest.withOpacity(0.3)) : (isPopular ? primaryColor.withOpacity(0.08) : Colors.white),
                            borderRadius: BorderRadius.circular(24),
                            border: Border.all(color: isPopular ? primaryColor : theme.colorScheme.outlineVariant, width: isPopular ? 2 : 1),
                            boxShadow: [
                              BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 10, offset: const Offset(0, 4)),
                            ],
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(planName, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w900)),
                                      if (isPopular)
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                          decoration: BoxDecoration(color: primaryColor, borderRadius: BorderRadius.circular(6)),
                                          child: const Text('POPULLOR', style: TextStyle(fontSize: 8.5, fontWeight: FontWeight.bold, color: Colors.white)),
                                        ),
                                    ],
                                  ),
                                  const SizedBox(height: 6),
                                  Row(
                                    crossAxisAlignment: CrossAxisAlignment.baseline,
                                    textBaseline: TextBaseline.alphabetic,
                                    children: [
                                      Text('${totalPrice.toStringAsFixed(2)} €', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900, color: primaryColor)),
                                      const SizedBox(width: 4),
                                      Text('/ $_selectedDurationMonths m.', style: const TextStyle(fontSize: 11, color: Colors.grey)),
                                    ],
                                  ),
                                  Text('~${monthlyAvg.toStringAsFixed(2)} €/muaj', style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: Colors.green)),

                                  const SizedBox(height: 10),
                                  const Divider(height: 1),
                                  const SizedBox(height: 10),

                                  _FeatureLine(text: 'Staf: ${plan['max_barbers'] != null && (int.tryParse(plan['max_barbers'].toString()) ?? 0) > 900 ? "PA LIMIT" : "Deri " + plan['max_barbers'].toString()}'),
                                  _FeatureLine(text: 'Shërbime: ${plan['max_services'] != null && (int.tryParse(plan['max_services'].toString()) ?? 0) > 900 ? "PA LIMIT" : "Deri " + plan['max_services'].toString()}'),
                                  const _FeatureLine(text: 'SMS Gateway & Firebase Push'),
                                ],
                              ),

                              SizedBox(
                                width: double.infinity,
                                child: ElevatedButton(
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: primaryColor,
                                    foregroundColor: Colors.white,
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                    padding: const EdgeInsets.symmetric(vertical: 10),
                                  ),
                                  onPressed: () => _openForm(null, plan, totalPrice),
                                  child: const Text('Zgjidh Planin 🚀', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                ),
                              ),
                            ],
                          ),
                        );
                      },
                    ),
                  ),

                const SizedBox(height: 20),

                // 📜 RENEWAL REQUESTS HISTORY SECTION
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Historiku i Kërkesave', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900)),
                    TextButton.icon(
                      onPressed: () => context.read<SubscriptionRenewalCubit>().refresh(),
                      icon: const Icon(Icons.refresh_rounded, size: 16),
                      label: const Text('Rifresko', style: TextStyle(fontSize: 12)),
                    ),
                  ],
                ),

                const SizedBox(height: 8),

                // List of Requests
                BlocConsumer<SubscriptionRenewalCubit, SubscriptionRenewalState>(
                  listener: (context, state) {
                    if (state is SubscriptionRenewalFailure) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(state.message), behavior: SnackBarBehavior.floating));
                  },
                  builder: (context, state) {
                    final items = state is SubscriptionRenewalLoaded ? state.items : state is SubscriptionRenewalLoading ? state.items : state is SubscriptionRenewalFailure ? state.items : const <Map<String, dynamic>>[];

                    if (state is SubscriptionRenewalLoading && items.isEmpty) {
                      return Column(
                        children: List.generate(3, (_) => const Padding(padding: EdgeInsets.only(bottom: 10), child: PremiumSkeleton())),
                      );
                    }

                    if (items.isEmpty) {
                      return PremiumEmptyState(
                        title: 'Asnjë kërkesë renovimi',
                        message: 'Nuk keni kryer ende kërkesa për renovimin e abonimit. Zgjidhni një plan më sipër për të filluar!',
                        icon: Icons.receipt_long_rounded,
                        actionLabel: canAdd ? 'Krijo Kërkesë të Re' : null,
                        action: canAdd ? () => _openForm() : null,
                      );
                    }

                    return ListView.separated(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: items.length,
                      separatorBuilder: (_, __) => const SizedBox(height: 10),
                      itemBuilder: (context, index) {
                        return SubscriptionRenewalCard(
                          item: items[index],
                          onTap: canEdit ? () => _openForm(items[index]) : null,
                          onDelete: canDelete ? () => _confirmDelete(context, items[index]) : null,
                        );
                      },
                    );
                  },
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _confirmDelete(BuildContext context, Map<String, dynamic> item) async {
    final ok = await PremiumDialog.confirm(
      context,
      title: 'Fshi Kërkesën',
      message: 'A jeni të sigurt që dëshironi të fshini këtë kërkesë renovimi?',
      confirmLabel: 'Fshi',
      destructive: true,
    );
    if (ok == true && context.mounted) context.read<SubscriptionRenewalCubit>().delete(item['id']);
  }
}

class _DurationTab extends StatelessWidget {
  final String label;
  final bool selected;
  final String? badge;
  final Color? badgeColor;
  final VoidCallback onTap;

  const _DurationTab({
    required this.label,
    required this.selected,
    this.badge,
    this.badgeColor,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Expanded(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 8),
          decoration: BoxDecoration(
            color: selected ? theme.colorScheme.primary : Colors.transparent,
            borderRadius: BorderRadius.circular(12),
          ),
          child: Column(
            children: [
              Text(
                label,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 10.5,
                  fontWeight: FontWeight.w900,
                  color: selected ? Colors.white : theme.colorScheme.onSurface,
                ),
              ),
              if (badge != null) ...[
                const SizedBox(height: 2),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                  decoration: BoxDecoration(
                    color: badgeColor ?? const Color(0xFF10B981),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    badge!,
                    style: const TextStyle(fontSize: 8, fontWeight: FontWeight.bold, color: Colors.white),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class _FeatureLine extends StatelessWidget {
  final String text;
  const _FeatureLine({required this.text});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: Row(
        children: [
          const Icon(Icons.check_circle_rounded, size: 13, color: Color(0xFF10B981)),
          const SizedBox(width: 5),
          Expanded(
            child: Text(text, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600)),
          ),
        ],
      ),
    );
  }
}
