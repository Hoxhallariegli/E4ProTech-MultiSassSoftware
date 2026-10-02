import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/core/widgets/app_scaffold.dart';
import 'package:mobile_gateway/modules/dashboard/presentation/widgets/shop_switcher_widget.dart';
import 'package:mobile_gateway/services/auth_service.dart';
import 'package:mobile_gateway/l10n/working_hour_localization.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';
import '../../data/working_hour_repository.dart';
import 'package:mobile_gateway/services/api_service.dart';

class WorkingHourListPage extends StatefulWidget {
  const WorkingHourListPage({super.key});

  @override
  State<WorkingHourListPage> createState() => _WorkingHourListPageState();
}

class _WorkingHourListPageState extends State<WorkingHourListPage> {
  final repository = WorkingHourRepository();
  int? _selectedBarberId;
  List<Map<String, dynamic>> _barbers = [];
  bool _loadingBarbers = true;
  bool _saving = false;

  // Map to hold local state for 7 days
  final List<String> _daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
  final Map<String, Map<String, dynamic>> _scheduleData = {};

  @override
  void initState() {
    super.initState();
    _loadBarbers();
  }

  String _formatTime(String? raw) {
    if (raw == null || raw.trim().isEmpty) return '';
    final parts = raw.trim().split(':');
    if (parts.length >= 2) {
      final h = parts[0].padLeft(2, '0');
      final m = parts[1].padLeft(2, '0');
      return '$h:$m';
    }
    return raw.trim();
  }

  Future<void> _loadBarbers() async {
    try {
      final res = await repository.lookup('barbers');
      if (!mounted) return;
      setState(() {
        _barbers = res;
        _loadingBarbers = false;
        if (_barbers.isNotEmpty) {
          _selectedBarberId = int.tryParse(_barbers.first['id'].toString());
        }
      });
      if (_selectedBarberId != null) {
        await _loadWeeklySchedule();
      }
    } catch (e) {
      if (mounted) setState(() => _loadingBarbers = false);
    }
  }

  Future<void> _loadWeeklySchedule() async {
    if (_selectedBarberId == null) return;
    setState(() => _loadingBarbers = true);

    try {
      final query = '?barber_id=$_selectedBarberId';
      final res = await ApiService.get('/working-hours$query');
      if (res.statusCode >= 200 && res.statusCode < 300) {
        final decoded = jsonDecode(res.body);
        final List items = decoded['data'] ?? [];

        // Reset
        for (var d in _daysOfWeek) {
          _scheduleData[d] = {
            'open_time': '08:00',
            'close_time': '20:00',
            'lunch_start': '',
            'lunch_end': '',
            'is_closed': true,
          };
        }

        // Fill with actual data
        for (var item in items) {
          final day = item['day_of_week']?.toString();
          if (day != null && _scheduleData.containsKey(day)) {
            final isClosedVal = item['is_closed'];
            final bool isClosed = isClosedVal == true ||
                isClosedVal == 1 ||
                isClosedVal == '1' ||
                isClosedVal == 'true';

            final open = _formatTime(item['open_time']?.toString());
            final close = _formatTime(item['close_time']?.toString());
            final lunchStart = _formatTime(item['lunch_start']?.toString());
            final lunchEnd = _formatTime(item['lunch_end']?.toString());

            _scheduleData[day] = {
              'open_time': open.isNotEmpty ? open : '08:00',
              'close_time': close.isNotEmpty ? close : '20:00',
              'lunch_start': lunchStart,
              'lunch_end': lunchEnd,
              'is_closed': isClosed,
            };
          }
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text('Gabim gjatë ngarkimit të orarit: ${e.toString().replaceAll('Exception: ', '')}'),
          behavior: SnackBarBehavior.floating,
          backgroundColor: Colors.red,
        ));
      }
    } finally {
      if (mounted) setState(() => _loadingBarbers = false);
    }
  }

  Future<void> _pickTime(String day, String fieldKey, {String defaultTime = '08:00'}) async {
    final currentStr = _scheduleData[day]![fieldKey]?.toString();
    final timeToUse = (currentStr != null && currentStr.isNotEmpty) ? currentStr : defaultTime;
    final parts = timeToUse.split(':');
    final initialHour = int.tryParse(parts[0]) ?? 12;
    final initialMinute = parts.length > 1 ? (int.tryParse(parts[1]) ?? 0) : 0;

    final picked = await showTimePicker(
      context: context,
      initialTime: TimeOfDay(hour: initialHour, minute: initialMinute),
    );

    if (picked != null && mounted) {
      final hour = picked.hour.toString().padLeft(2, '0');
      final minute = picked.minute.toString().padLeft(2, '0');
      setState(() {
        _scheduleData[day]![fieldKey] = '$hour:$minute';
      });
    }
  }

  Future<void> _saveWeeklySchedule() async {
    if (_selectedBarberId == null) return;
    setState(() => _saving = true);

    try {
      final payload = {
        'barber_id': _selectedBarberId,
        'days': _scheduleData,
      };

      final res = await ApiService.post('/working-hours', payload);
      if (res.statusCode >= 200 && res.statusCode < 300) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
            content: Text('Orari javor u ruajt me sukses!'),
            behavior: SnackBarBehavior.floating,
            backgroundColor: Colors.green,
          ));
        }
        await _loadWeeklySchedule();
      } else {
        throw Exception(ApiService.extractErrorMessage(res));
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(e.toString().replaceAll('Exception: ', '')),
          behavior: SnackBarBehavior.floating,
          backgroundColor: Colors.red,
        ));
      }
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return AppScaffold(
      currentNavIndex: 1,
      showBackButton: true,
      title: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Orari Javor i Punës', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
          InkWell(
            onTap: () {
              showModalBottomSheet(
                context: context,
                isScrollControlled: true,
                backgroundColor: Colors.transparent,
                builder: (_) => ShopSwitcherWidget(onSwitched: () => _loadBarbers()),
              );
            },
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(AuthService.instance.user?['business']?['name'] ?? 'Select Shop', style: TextStyle(fontSize: 10, color: theme.colorScheme.primary, fontWeight: FontWeight.bold)),
                Icon(Icons.keyboard_arrow_down_rounded, size: 12, color: theme.colorScheme.primary),
              ],
            ),
          ),
        ],
      ),
      actions: [
        IconButton(
          tooltip: 'Rifresko',
          onPressed: () => _loadWeeklySchedule(),
          icon: const Icon(Icons.refresh_rounded),
        ),
        const SizedBox(width: 8),
      ],
      body: _loadingBarbers
          ? const Center(child: CircularProgressIndicator.adaptive())
          : Column(
              children: [
                // Barber Selector Header
                Container(
                  padding: const EdgeInsets.all(16),
                  margin: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: theme.colorScheme.surfaceContainerLow,
                    borderRadius: BorderRadius.circular(24),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.person_outline_rounded, color: Colors.grey),
                      const SizedBox(width: 12),
                      Text('${context.staffLabel}:', style: const TextStyle(fontWeight: FontWeight.bold)),
                      const SizedBox(width: 12),
                      Expanded(
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<int>(
                            value: _selectedBarberId,
                            isExpanded: true,
                            items: _barbers.map((b) {
                              return DropdownMenuItem<int>(
                                value: int.tryParse(b['id'].toString()),
                                child: Text(b['name']?.toString() ?? context.staffLabel, style: const TextStyle(fontWeight: FontWeight.w800)),
                              );
                            }).toList(),
                            onChanged: (v) {
                              if (v != null) {
                                setState(() {
                                  _selectedBarberId = v;
                                });
                                _loadWeeklySchedule();
                              }
                            },
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                // Days List
                Expanded(
                  child: ListView.separated(
                    padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
                    itemCount: _daysOfWeek.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 12),
                    itemBuilder: (context, index) {
                      final day = _daysOfWeek[index];
                      final dayData = _scheduleData[day] ?? {
                        'open_time': '08:00',
                        'close_time': '20:00',
                        'lunch_start': '',
                        'lunch_end': '',
                        'is_closed': true
                      };
                      final isClosed = dayData['is_closed'] == true;

                      return PremiumCard(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                // Day Name Label
                                SizedBox(
                                  width: 80,
                                  child: Text(
                                    day.substring(0, 3).toUpperCase(),
                                    style: TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.w900,
                                      color: isClosed ? Colors.grey : theme.colorScheme.primary,
                                    ),
                                  ),
                                ),

                                // Time pickers
                                Expanded(
                                  child: Opacity(
                                    opacity: isClosed ? 0.3 : 1.0,
                                    child: Row(
                                      children: [
                                        Expanded(
                                          child: InkWell(
                                            onTap: isClosed ? null : () => _pickTime(day, 'open_time', defaultTime: '08:00'),
                                            child: Container(
                                              padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 10),
                                              decoration: BoxDecoration(
                                                color: theme.colorScheme.surfaceContainerHighest,
                                                borderRadius: BorderRadius.circular(12),
                                              ),
                                              child: Text(
                                                (dayData['open_time']?.toString().isNotEmpty == true) ? dayData['open_time'].toString() : '08:00',
                                                textAlign: TextAlign.center,
                                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                              ),
                                            ),
                                          ),
                                        ),
                                        const Padding(
                                          padding: EdgeInsets.symmetric(horizontal: 4),
                                          child: Text('-'),
                                        ),
                                        Expanded(
                                          child: InkWell(
                                            onTap: isClosed ? null : () => _pickTime(day, 'close_time', defaultTime: '20:00'),
                                            child: Container(
                                              padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 10),
                                              decoration: BoxDecoration(
                                                color: theme.colorScheme.surfaceContainerHighest,
                                                borderRadius: BorderRadius.circular(12),
                                              ),
                                              child: Text(
                                                (dayData['close_time']?.toString().isNotEmpty == true) ? dayData['close_time'].toString() : '20:00',
                                                textAlign: TextAlign.center,
                                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                              ),
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),

                                const SizedBox(width: 12),

                                // Pushim / Closed Switch
                                Column(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Switch.adaptive(
                                      value: isClosed,
                                      activeColor: Colors.red,
                                      onChanged: (v) {
                                        setState(() {
                                          _scheduleData[day]!['is_closed'] = v;
                                        });
                                      },
                                    ),
                                    const Text('Pushim', style: TextStyle(fontSize: 10, color: Colors.grey, fontWeight: FontWeight.bold)),
                                  ],
                                ),
                              ],
                            ),

                            if (!isClosed) ...[
                              const SizedBox(height: 10),
                              const Divider(height: 1, thickness: 0.5),
                              const SizedBox(height: 8),
                              Row(
                                children: [
                                  const SizedBox(
                                    width: 80,
                                    child: Row(
                                      children: [
                                        Icon(Icons.restaurant_rounded, size: 14, color: Colors.amber),
                                        SizedBox(width: 4),
                                        Text('Dreka:', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.amber)),
                                      ],
                                    ),
                                  ),
                                  Expanded(
                                    child: Row(
                                      children: [
                                        Expanded(
                                          child: InkWell(
                                            onTap: () => _pickTime(day, 'lunch_start', defaultTime: '13:00'),
                                            child: Container(
                                              padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 10),
                                              decoration: BoxDecoration(
                                                color: Colors.amber.withOpacity(0.12),
                                                borderRadius: BorderRadius.circular(10),
                                                border: Border.all(color: Colors.amber.withOpacity(0.3)),
                                              ),
                                              child: Text(
                                                (dayData['lunch_start']?.toString().isNotEmpty == true) ? dayData['lunch_start'].toString() : 'Nga (13:00)',
                                                textAlign: TextAlign.center,
                                                style: TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 12,
                                                  color: (dayData['lunch_start']?.toString().isNotEmpty == true) ? theme.colorScheme.onSurface : Colors.grey,
                                                ),
                                              ),
                                            ),
                                          ),
                                        ),
                                        const Padding(
                                          padding: EdgeInsets.symmetric(horizontal: 4),
                                          child: Text('-', style: TextStyle(fontSize: 11)),
                                        ),
                                        Expanded(
                                          child: InkWell(
                                            onTap: () => _pickTime(day, 'lunch_end', defaultTime: '14:00'),
                                            child: Container(
                                              padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 10),
                                              decoration: BoxDecoration(
                                                color: Colors.amber.withOpacity(0.12),
                                                borderRadius: BorderRadius.circular(10),
                                                border: Border.all(color: Colors.amber.withOpacity(0.3)),
                                              ),
                                              child: Text(
                                                (dayData['lunch_end']?.toString().isNotEmpty == true) ? dayData['lunch_end'].toString() : 'Deri (14:00)',
                                                textAlign: TextAlign.center,
                                                style: TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 12,
                                                  color: (dayData['lunch_end']?.toString().isNotEmpty == true) ? theme.colorScheme.onSurface : Colors.grey,
                                                ),
                                              ),
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  if ((dayData['lunch_start']?.toString().isNotEmpty == true) || (dayData['lunch_end']?.toString().isNotEmpty == true)) ...[
                                    const SizedBox(width: 4),
                                    IconButton(
                                      icon: const Icon(Icons.close_rounded, size: 16, color: Colors.grey),
                                      padding: EdgeInsets.zero,
                                      constraints: const BoxConstraints(),
                                      onPressed: () {
                                        setState(() {
                                          _scheduleData[day]!['lunch_start'] = '';
                                          _scheduleData[day]!['lunch_end'] = '';
                                        });
                                      },
                                    ),
                                  ],
                                ],
                              ),
                            ],
                          ],
                        ),
                      );
                    },
                  ),
                ),

                // Save Button Footer
                SafeArea(
                  top: false,
                  child: Padding(
                    padding: EdgeInsets.fromLTRB(
                      16,
                      12,
                      16,
                      16 + MediaQuery.of(context).padding.bottom,
                    ),
                    child: PremiumButton(
                      onPressed: _saving ? null : _saveWeeklySchedule,
                      label: _saving ? 'Duke ruajtur...' : 'Ruaj Orarin Javor',
                      icon: Icons.check_rounded,
                      expand: true,
                    ),
                  ),
                ),
              ],
            ),
    );
  }
}
