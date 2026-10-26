import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';
import 'package:mobile_gateway/core/widgets/app_scaffold.dart';
import 'package:mobile_gateway/modules/dashboard/presentation/widgets/shop_switcher_widget.dart';
import 'package:mobile_gateway/l10n/report_localization.dart';
import 'package:mobile_gateway/l10n/core_localization.dart';
import '../../data/report_repository.dart';

class ReportListPage extends StatefulWidget {
  const ReportListPage({super.key});

  @override
  State<ReportListPage> createState() => _ReportListPageState();
}

class _ReportListPageState extends State<ReportListPage> {
  final repository = ReportRepository();
  bool _loading = true;

  String _periodType = 'daily'; // daily, weekly, monthly
  DateTime _selectedDate = DateTime.now();
  int? _selectedBarberId;
  List<Map<String, dynamic>> _barberOptions = [];

  Map<String, dynamic>? _generatedMetrics;
  List<Map<String, dynamic>> _periodBookings = [];

  @override
  void initState() {
    super.initState();
    _loadBarbersAndGenerate();
  }

  Future<void> _loadBarbersAndGenerate() async {
    setState(() => _loading = true);
    try {
      final rawBarbers = await repository.lookup('barbers');
      _barberOptions = rawBarbers;
      await _fetchReportData();
    } catch (e) {
      debugPrint('Error loading report data: $e');
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _fetchReportData() async {
    final dateStr = '${_selectedDate.year.toString().padLeft(4, '0')}-${_selectedDate.month.toString().padLeft(2, '0')}-${_selectedDate.day.toString().padLeft(2, '0')}';
    final query = <String, String>{
      'period_type': _periodType,
      'report_date': dateStr,
      if (_selectedBarberId != null) 'barber_id': _selectedBarberId.toString(),
    };

    try {
      final res = await ApiService.get('/reports/generate?' + Uri(queryParameters: query).query);
      if (res.statusCode == 200) {
        final decoded = jsonDecode(res.body);
        _generatedMetrics = decoded['data'] != null ? Map<String, dynamic>.from(decoded['data']) : null;
      }

      // Fetch bookings list for period breakdown
      final bookingsRes = await ApiService.get('/bookings?per_page=50');
      if (bookingsRes.statusCode == 200) {
        final decodedBookings = jsonDecode(bookingsRes.body);
        final list = (decodedBookings['data'] as List? ?? []).map((e) => Map<String, dynamic>.from(e as Map)).toList();

        // Filter bookings by date range locally for exact period accuracy
        DateTime startDate = DateTime(_selectedDate.year, _selectedDate.month, _selectedDate.day);
        DateTime endDate = startDate.add(const Duration(days: 1));

        if (_periodType == 'weekly') {
          startDate = _selectedDate.subtract(Duration(days: _selectedDate.weekday - 1));
          endDate = startDate.add(const Duration(days: 7));
        } else if (_periodType == 'monthly') {
          startDate = DateTime(_selectedDate.year, _selectedDate.month, 1);
          endDate = DateTime(_selectedDate.year, _selectedDate.month + 1, 1);
        }

        _periodBookings = list.where((b) {
          final apptStr = b['appointment_at']?.toString();
          if (apptStr == null) return false;
          final appt = DateTime.tryParse(apptStr)?.toLocal();
          if (appt == null) return false;

          final matchesDate = appt.isAfter(startDate.subtract(const Duration(seconds: 1))) && appt.isBefore(endDate);
          final matchesBarber = _selectedBarberId == null || b['barber_id']?.toString() == _selectedBarberId.toString();

          return matchesDate && matchesBarber;
        }).toList();

        // Calculate total revenue locally if API returned 0
        if (_generatedMetrics == null || (_generatedMetrics!['total_revenue'] ?? 0) == 0) {
          double calculatedRevenue = 0.0;
          for (final b in _periodBookings) {
            final status = (b['status'] ?? '').toString();
            final paymentStatus = (b['payment_status'] ?? '').toString();
            final price = double.tryParse(b['total_price']?.toString() ?? '0') ?? 0.0;

            if (status != 'cancelled' && (['completed', 'paid', 'confirmed'].contains(status) || paymentStatus == 'paid')) {
              calculatedRevenue += price;
            }
          }

          _generatedMetrics = {
            'total_bookings': _periodBookings.length,
            'total_revenue': calculatedRevenue,
            'total_completed': _periodBookings.where((b) => (b['status'] ?? '').toString() == 'completed').length,
            'total_cancelled': _periodBookings.where((b) => (b['status'] ?? '').toString() == 'cancelled').length,
          };
        }
      }
    } catch (e) {
      debugPrint('Error fetching report metrics: $e');
    } if (mounted) setState(() {});
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime(2020),
      lastDate: DateTime(2030),
    );
    if (picked != null) {
      setState(() => _selectedDate = picked);
      _fetchReportData();
    }
  }

  void _pickBarber() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(28))),
      builder: (_) => Container(
        padding: const EdgeInsets.all(20),
        height: MediaQuery.of(context).size.height * 0.5,
        child: Column(
          children: [
            Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
            const SizedBox(height: 16),
            Text(reportTr(context, 'field.barber_id'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
            const SizedBox(height: 12),
            ListTile(
              leading: const Icon(Icons.people_alt_rounded),
              title: Text(reportTr(context, 'list.clear') == 'Pastro' ? 'Gjithë Stafi' : 'All Staff', style: const TextStyle(fontWeight: FontWeight.bold)),
              trailing: _selectedBarberId == null ? const Icon(Icons.check_circle_rounded, color: Colors.blue) : null,
              onTap: () {
                Navigator.pop(context);
                setState(() => _selectedBarberId = null);
                _fetchReportData();
              },
            ),
            const Divider(),
            Expanded(
              child: ListView.builder(
                itemCount: _barberOptions.length,
                itemBuilder: (context, index) {
                  final barber = _barberOptions[index];
                  final id = int.tryParse(barber['id']?.toString() ?? '');
                  final name = barber['name']?.toString() ?? 'Barber';
                  final selected = _selectedBarberId == id;

                  return ListTile(
                    leading: const Icon(Icons.person_pin_rounded),
                    title: Text(name, style: const TextStyle(fontWeight: FontWeight.bold)),
                    trailing: selected ? const Icon(Icons.check_circle_rounded, color: Colors.blue) : null,
                    onTap: () {
                      Navigator.pop(context);
                      setState(() => _selectedBarberId = id);
                      _fetchReportData();
                    },
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final primaryColor = theme.colorScheme.primary;

    final totalRevenue = double.tryParse(_generatedMetrics?['total_revenue']?.toString() ?? '0') ?? 0.0;
    final totalBookings = int.tryParse(_generatedMetrics?['total_bookings']?.toString() ?? '0') ?? 0;
    final totalCompleted = int.tryParse(_generatedMetrics?['total_completed']?.toString() ?? '0') ?? 0;
    final totalCancelled = int.tryParse(_generatedMetrics?['total_cancelled']?.toString() ?? '0') ?? 0;

    final selectedBarberName = _selectedBarberId != null
        ? (_barberOptions.firstWhere((e) => e['id']?.toString() == _selectedBarberId.toString(), orElse: () => {'name': 'Stafi'})['name'] ?? 'Stafi')
        : (reportTr(context, 'list.clear') == 'Pastro' ? 'Gjithë Stafi' : 'All Staff');

    return AppScaffold(
      currentNavIndex: 1,
      title: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(reportTr(context, 'list.title'), style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w900)),
          InkWell(
            onTap: () {
              showModalBottomSheet(
                context: context,
                isScrollControlled: true,
                backgroundColor: Colors.transparent,
                builder: (_) => ShopSwitcherWidget(onSwitched: () => _fetchReportData()),
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
        ],
      ),
      actions: [
        IconButton(
          tooltip: reportTr(context, 'list.refresh'),
          onPressed: _fetchReportData,
          icon: const Icon(Icons.refresh_rounded),
        ),
        const SizedBox(width: 8),
      ],
      body: _loading
          ? const Center(child: CircularProgressIndicator.adaptive())
          : RefreshIndicator(
              onRefresh: _fetchReportData,
              child: ListView(
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 120),
                children: [
                  // 1. FILTER CONTROLS CARD
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0xFF1E212B) : Colors.white,
                      borderRadius: BorderRadius.circular(22),
                      border: Border.all(color: isDark ? const Color(0xFF3B4052) : Colors.grey.shade200),
                      boxShadow: [
                        BoxShadow(color: Colors.black.withOpacity(0.03), blurRadius: 10, offset: const Offset(0, 4)),
                      ],
                    ),
                    child: Column(
                      children: [
                        // Period Switcher
                        Container(
                          padding: const EdgeInsets.all(4),
                          decoration: BoxDecoration(
                            color: isDark ? Colors.black38 : Colors.grey.shade100,
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Row(
                            children: [
                              _PeriodTab(
                                label: reportTr(context, 'field.period_type') == 'Period Type' ? 'Daily' : 'Ditore',
                                selected: _periodType == 'daily',
                                onTap: () {
                                  setState(() => _periodType = 'daily');
                                  _fetchReportData();
                                },
                              ),
                              _PeriodTab(
                                label: reportTr(context, 'field.period_type') == 'Period Type' ? 'Weekly' : 'Javore',
                                selected: _periodType == 'weekly',
                                onTap: () {
                                  setState(() => _periodType = 'weekly');
                                  _fetchReportData();
                                },
                              ),
                              _PeriodTab(
                                label: reportTr(context, 'field.period_type') == 'Period Type' ? 'Monthly' : 'Mujore',
                                selected: _periodType == 'monthly',
                                onTap: () {
                                  setState(() => _periodType = 'monthly');
                                  _fetchReportData();
                                },
                              ),
                            ],
                          ),
                        ),

                        const SizedBox(height: 12),

                        Row(
                          children: [
                            // Date Picker Button
                            Expanded(
                              child: InkWell(
                                onTap: _pickDate,
                                borderRadius: BorderRadius.circular(14),
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                  decoration: BoxDecoration(
                                    border: Border.all(color: theme.colorScheme.outlineVariant),
                                    borderRadius: BorderRadius.circular(14),
                                  ),
                                  child: Row(
                                    children: [
                                      Icon(Icons.calendar_today_rounded, size: 16, color: primaryColor),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: Text(
                                          '${_selectedDate.day.toString().padLeft(2, '0')}/${_selectedDate.month.toString().padLeft(2, '0')}/${_selectedDate.year}',
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),

                            // Barber Picker Button
                            Expanded(
                              child: InkWell(
                                onTap: _pickBarber,
                                borderRadius: BorderRadius.circular(14),
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                  decoration: BoxDecoration(
                                    border: Border.all(color: theme.colorScheme.outlineVariant),
                                    borderRadius: BorderRadius.circular(14),
                                  ),
                                  child: Row(
                                    children: [
                                      Icon(Icons.person_pin_rounded, size: 16, color: primaryColor),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: Text(
                                          selectedBarberName,
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

                  // 2. 4 HERO KPI STAT CARDS GRID
                  GridView.count(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    crossAxisCount: 2,
                    crossAxisSpacing: 10,
                    mainAxisSpacing: 10,
                    childAspectRatio: 1.5,
                    children: [
                      _StatCard(
                        title: reportTr(context, 'field.total_revenue'),
                        value: context.formatCurrency(totalRevenue),
                        icon: Icons.account_balance_wallet_rounded,
                        color: const Color(0xFF10B981),
                      ),
                      _StatCard(
                        title: reportTr(context, 'field.total_bookings'),
                        value: totalBookings.toString(),
                        icon: Icons.event_available_rounded,
                        color: primaryColor,
                      ),
                      _StatCard(
                        title: reportTr(context, 'field.total_completed'),
                        value: totalCompleted.toString(),
                        icon: Icons.check_circle_rounded,
                        color: const Color(0xFF3B82F6),
                      ),
                      _StatCard(
                        title: reportTr(context, 'field.total_cancelled'),
                        value: totalCancelled.toString(),
                        icon: Icons.cancel_rounded,
                        color: const Color(0xFFEF4444),
                      ),
                    ],
                  ),

                  const SizedBox(height: 20),

                  // 3. PERIOD BOOKINGS BREAKDOWN LIST
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        reportTr(context, 'list.title') == 'Reports & Analytics' ? 'Period Bookings Details' : 'Detajet e Takimeve të Periudhës',
                        style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900),
                      ),
                      Text(
                        '${_periodBookings.length} Takime',
                        style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: primaryColor),
                      ),
                    ],
                  ),

                  const SizedBox(height: 10),

                  if (_periodBookings.isEmpty)
                    PremiumEmptyState(
                      title: reportTr(context, 'list.nothing'),
                      message: reportTr(context, 'list.title') == 'Reports & Analytics'
                          ? 'No bookings found for selected date and period.'
                          : 'Nuk u gjet asnjë takim për këtë datë/periudhë të zgjedhur.',
                      icon: Icons.event_busy_rounded,
                    )
                  else
                    ListView.separated(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: _periodBookings.length,
                      separatorBuilder: (_, __) => const SizedBox(height: 8),
                      itemBuilder: (context, index) {
                        final booking = _periodBookings[index];
                        final customerName = booking['customer']?['name'] ?? 'Klient Walk-in';
                        final phone = booking['customer']?['phone'] ?? '';
                        final serviceName = booking['service']?['name'] ?? 'Shërbim';
                        final barberName = booking['barber']?['name'] ?? 'Stafi';
                        final price = double.tryParse(booking['total_price']?.toString() ?? '0') ?? 0.0;
                        final status = (booking['status'] ?? 'pending').toString();
                        final apptStr = booking['appointment_at']?.toString() ?? '';
                        final appt = DateTime.tryParse(apptStr)?.toLocal();
                        final formattedTime = appt != null
                            ? '${appt.day.toString().padLeft(2, '0')}/${appt.month.toString().padLeft(2, '0')} ${appt.hour.toString().padLeft(2, '0')}:${appt.minute.toString().padLeft(2, '0')}'
                            : '-';

                        return Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: isDark ? const Color(0xFF1E212B) : Colors.white,
                            borderRadius: BorderRadius.circular(18),
                            border: Border.all(color: isDark ? const Color(0xFF3B4052) : Colors.grey.shade200),
                          ),
                          child: Row(
                            children: [
                              Container(
                                width: 40,
                                height: 40,
                                decoration: BoxDecoration(
                                  color: primaryColor.withOpacity(0.12),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Icon(Icons.calendar_month_rounded, color: primaryColor, size: 20),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(customerName, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
                                    const SizedBox(height: 2),
                                    Text('$serviceName • $barberName', style: const TextStyle(fontSize: 11, color: Colors.grey, fontWeight: FontWeight.w600)),
                                    Text(formattedTime, style: TextStyle(fontSize: 10.5, color: primaryColor, fontWeight: FontWeight.bold)),
                                  ],
                                ),
                              ),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  Text(context.formatCurrency(price), style: TextStyle(fontWeight: FontWeight.w900, fontSize: 15, color: primaryColor)),
                                  const SizedBox(height: 4),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                    decoration: BoxDecoration(
                                      color: status == 'completed'
                                          ? const Color(0xFF10B981).withOpacity(0.15)
                                          : (status == 'cancelled' ? Colors.red.withOpacity(0.15) : Colors.amber.withOpacity(0.15)),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Text(
                                      status.toUpperCase(),
                                      style: TextStyle(
                                        fontSize: 9,
                                        fontWeight: FontWeight.w900,
                                        color: status == 'completed'
                                            ? const Color(0xFF10B981)
                                            : (status == 'cancelled' ? Colors.red : Colors.amber.shade800),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        );
                      },
                    ),
                ],
              ),
            ),
    );
  }
}

class _PeriodTab extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _PeriodTab({
    required this.label,
    required this.selected,
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
          child: Text(
            label,
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w900,
              color: selected ? Colors.white : theme.colorScheme.onSurface,
            ),
          ),
        ),
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;
  final Color color;

  const _StatCard({
    required this.title,
    required this.value,
    required this.icon,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E212B) : Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: isDark ? const Color(0xFF3B4052) : Colors.grey.shade200),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 6, offset: const Offset(0, 2)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.12),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: color, size: 18),
              ),
            ],
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(value, style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: color)),
              const SizedBox(height: 1),
              Text(title, style: const TextStyle(fontSize: 9.5, color: Colors.grey, fontWeight: FontWeight.bold), maxLines: 1, overflow: TextOverflow.ellipsis),
            ],
          ),
        ],
      ),
    );
  }
}
