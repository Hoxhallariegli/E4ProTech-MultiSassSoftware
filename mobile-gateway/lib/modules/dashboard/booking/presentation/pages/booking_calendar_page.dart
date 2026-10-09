import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';
import 'package:mobile_gateway/modules/dashboard/presentation/widgets/shop_switcher_widget.dart';
import 'package:mobile_gateway/modules/dashboard/payment/presentation/pages/payment_form_page.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';
import 'package:mobile_gateway/l10n/booking_localization.dart';
import 'package:mobile_gateway/core/realtime/realtime_service.dart';
import '../cubit/booking_cubit.dart';
import '../cubit/booking_state.dart';
import '../../data/booking_repository.dart';
import 'booking_form_page.dart';

class BookingCalendarPage extends StatefulWidget {
  const BookingCalendarPage({super.key});
  @override State<BookingCalendarPage> createState() => BookingCalendarPageState();
}

class BookingCalendarPageState extends State<BookingCalendarPage> {
  DateTime _focusedDay = DateTime.now();
  DateTime _selectedDay = DateTime(DateTime.now().year, DateTime.now().month, DateTime.now().day);
  int? _selectedBarberId;
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    AuthService.instance.addListener(_onAuthChanged);
    refreshAll(reloadBarbers: true);

    RealtimeService.instance.subscribe('bookings', (action, data) {
      if (!mounted) return;
      refreshAll(reloadBarbers: false);
    }, tenantId: AuthService.instance.user?['barber_shop_id']);
  }

  @override
  void dispose() {
    AuthService.instance.removeListener(_onAuthChanged);
    _scrollController.dispose();
    super.dispose();
  }

  int? _lastShopId;

  void _onAuthChanged() {
    final currentShopId = AuthService.instance.currentBarberShopId;
    if (mounted && currentShopId != _lastShopId) {
      _lastShopId = currentShopId;
      refreshAll(reloadBarbers: true);
    }
  }

  void _scrollToCurrentTime(List<dynamic> displaySlots) {
    if (displaySlots.isEmpty) return;
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final isSelectedToday = _selectedDay.year == today.year && _selectedDay.month == today.month && _selectedDay.day == today.day;
    if (!isSelectedToday) return;

    int targetIndex = 0;
    int minDiff = 999999;

    for (int i = 0; i < displaySlots.length; i++) {
      final slot = displaySlots[i];
      final time = (slot['time'] ?? '00:00').toString();
      final parts = time.split(':');
      final slotTime = DateTime(
        _selectedDay.year,
        _selectedDay.month,
        _selectedDay.day,
        int.tryParse(parts[0]) ?? 0,
        int.tryParse(parts.length > 1 ? parts[1] : '0') ?? 0,
      );
      final diff = slotTime.difference(now).inMinutes;
      if (diff >= 0 && diff < minDiff) {
        minDiff = diff;
        targetIndex = i;
        break;
      }
    }

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      if (_scrollController.hasClients && _scrollController.position.hasContentDimensions) {
        try {
          final position = _scrollController.position;
          final itemOffset = targetIndex * 75.0;
          final viewportHalf = (position.hasViewportDimension && position.viewportDimension > 0)
              ? position.viewportDimension / 2
              : 200.0;
          final maxExtent = position.hasContentDimensions ? position.maxScrollExtent : 0.0;
          final centeredOffset = (itemOffset - viewportHalf + 37.5).clamp(
            0.0,
            maxExtent,
          );
          if (centeredOffset >= 0) {
            _scrollController.animateTo(
              centeredOffset,
              duration: const Duration(milliseconds: 600),
              curve: Curves.easeInOut,
            );
          }
        } catch (_) {}
      }
    });
  }

  Future<void> refreshAll({bool reloadBarbers = true}) async {
    if (!mounted) return;
    context.read<BookingCubit>().refreshCalendarData(
      month: _focusedDay,
      day: _selectedDay,
      preferredBarberId: reloadBarbers ? null : _selectedBarberId,
      reloadBarbersList: reloadBarbers,
    );
  }

  void _refresh() {
    refreshAll(reloadBarbers: false);
  }

  String _formatDate(DateTime day) {
    return '${day.year.toString().padLeft(4, '0')}-'
        '${day.month.toString().padLeft(2, '0')}-'
        '${day.day.toString().padLeft(2, '0')}';
  }

  void _loadSelectedDay(DateTime day) {
    if (!mounted) return;

    final cleanDay = DateTime(day.year, day.month, day.day);

    setState(() {
      _selectedDay = cleanDay;
      _focusedDay = cleanDay;
    });

    context.read<BookingCubit>().refreshCalendarData(
      month: cleanDay,
      day: cleanDay,
      preferredBarberId: _selectedBarberId,
      reloadBarbersList: false,
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      backgroundColor: theme.colorScheme.surface,
      body: SafeArea(
        child: Column(
          children: [
            // Red Offline Banner
            ValueListenableBuilder<bool>(
              valueListenable: ApiService.isOffline,
              builder: (context, offline, child) {
                if (!offline) return const SizedBox.shrink();
                return Container(
                  width: double.infinity,
                  color: Colors.red.shade800,
                  padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 16),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.wifi_off_rounded, color: Colors.white, size: 14),
                      const SizedBox(width: 8),
                      const Expanded(
                        child: Text(
                          'Nuk ka lidhje me serverin (Aplikacioni është Offline)',
                          style: TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold),
                          textAlign: TextAlign.center,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      InkWell(
                        onTap: () {
                          ApiService.checkServerHealth();
                        },
                        child: const Icon(Icons.refresh_rounded, color: Colors.white, size: 16),
                      ),
                    ],
                  ),
                );
              },
            ),

            // Green Online Restored Banner
            ValueListenableBuilder<bool>(
              valueListenable: ApiService.isOnlineRestored,
              builder: (context, onlineRestored, child) {
                if (!onlineRestored) return const SizedBox.shrink();
                return Container(
                  width: double.infinity,
                  color: Colors.green.shade800,
                  padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 16),
                  child: const Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.wifi_rounded, color: Colors.white, size: 14),
                      SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          'Lidhja me serverin u rikthye (Online) 📶',
                          style: TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold),
                          textAlign: TextAlign.center,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                );
              },
            ),

            Expanded(
              child: BlocConsumer<BookingCubit, BookingState>(
                listener: (context, state) {
                  if (state is BookingFailure) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text(state.message), behavior: SnackBarBehavior.floating),
                    );
                  }
                },
                builder: (context, state) {
                  final isCalendarLoaded = state is BookingCalendarLoaded;
                  final barbers = isCalendarLoaded ? state.barbers : const <Map<String, dynamic>>[];
                  final calendarStats = isCalendarLoaded ? state.calendarStats : const <String, int>{};
                  final daySlots = isCalendarLoaded ? state.daySlots : const [];
                  final isLoading = isCalendarLoaded ? state.isLoading : true;
                  final calendarMessage = isCalendarLoaded ? state.calendarMessage : null;

                  if (isCalendarLoaded && state.selectedBarberId != null) {
                    _selectedBarberId = state.selectedBarberId;
                  }

                  return Column(
                    children: [
                      _buildBarberFilter(barbers),
                      const SizedBox(height: 8),
                      _buildCustomWeekCalendar(calendarStats),
                      const SizedBox(height: 4),
                      Expanded(
                        child: isLoading
                            ? const Center(child: CircularProgressIndicator.adaptive())
                            : _buildFilteredTimeline(daySlots, calendarMessage),
                      ),
                    ],
                  );
                },
              ),
            ),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton(
        backgroundColor: theme.colorScheme.primary,
        onPressed: () async {
          final res = await Navigator.push(
            context,
            MaterialPageRoute(builder: (c) => const BookingFormPage()),
          );
          if (res == true) _refresh();
        },
        child: const Icon(Icons.add, color: Colors.white, size: 28),
      ),
    );
  }

  Widget _buildBarberFilter(List<Map<String, dynamic>> barbers) {
    if (barbers.isEmpty) return const SizedBox.shrink();

    return SizedBox(
      height: 60,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        children: barbers.map((b) => _barberChip((b['name'] ?? context.staffLabel).toString(), int.tryParse(b['id']?.toString() ?? ''))).toList(),
      ),
    );
  }

  Widget _barberChip(String label, int? id) {
    final theme = Theme.of(context);
    final isSelected = _selectedBarberId == id;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: ChoiceChip(
        label: Text(label),
        selected: isSelected,
        selectedColor: theme.colorScheme.primary,
        backgroundColor: theme.colorScheme.surfaceContainerHighest,
        labelStyle: TextStyle(
          color: isSelected ? Colors.white : theme.colorScheme.onSurface,
          fontSize: 12,
          fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
        ),
        onSelected: (_) {
          setState(() => _selectedBarberId = id);
          _refresh();
        },
      ),
    );
  }

  Widget _buildCustomWeekCalendar(Map<String, int> stats) {
    final theme = Theme.of(context);
    final startDay = DateTime.now().subtract(const Duration(days: 4));

    return Container(
      height: 84,
      padding: const EdgeInsets.symmetric(vertical: 8),
      decoration: BoxDecoration(
        color: theme.colorScheme.surface,
        border: Border(bottom: BorderSide(color: theme.dividerColor, width: 0.5)),
      ),
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 12),
        itemCount: 14,
        itemBuilder: (context, index) {
          final day = startDay.add(Duration(days: index));
          final isSelected = day.year == _selectedDay.year && day.month == _selectedDay.month && day.day == _selectedDay.day;
          final dateStr = "${day.year}-${day.month.toString().padLeft(2, '0')}-${day.day.toString().padLeft(2, '0')}";
          final hasBookings = (stats[dateStr] ?? 0) > 0;

          final isEn = Localizations.localeOf(context).languageCode == 'en';
          final weekDays = isEn
              ? ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
              : ['Dje', 'Hën', 'Mar', 'Mër', 'Enj', 'Pre', 'Sht'];
          final dayName = weekDays[day.weekday % 7];

          return GestureDetector(
            onTap: () => _loadSelectedDay(day),
            child: Container(
              width: 54,
              margin: const EdgeInsets.symmetric(horizontal: 4),
              decoration: BoxDecoration(
                color: isSelected ? theme.colorScheme.primary : theme.colorScheme.surfaceContainerHighest.withOpacity(0.3),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: isSelected ? theme.colorScheme.primary : theme.colorScheme.outlineVariant,
                  width: 1,
                ),
              ),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    dayName,
                    style: TextStyle(
                      fontSize: 11,
                      color: isSelected ? Colors.white : Colors.grey,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    day.day.toString(),
                    style: TextStyle(
                      fontSize: 15,
                      color: isSelected ? Colors.white : theme.colorScheme.onSurface,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  if (hasBookings) ...[
                    const SizedBox(height: 4),
                    Container(
                      width: 5,
                      height: 5,
                      decoration: BoxDecoration(
                        color: isSelected ? Colors.white : theme.colorScheme.primary,
                        shape: BoxShape.circle,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildFilteredTimeline(List<dynamic> slots, String? message) {
    final theme = Theme.of(context);

    if (_selectedBarberId == null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Text(
            'Zgjidhni një ${context.staffLabel.toLowerCase()} për të parë axhendën.',
            style: const TextStyle(color: Colors.grey, fontWeight: FontWeight.bold),
          ),
        ),
      );
    }

    if (slots.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.event_busy_rounded, size: 48, color: theme.colorScheme.outlineVariant),
            const SizedBox(height: 12),
            Text(
              message ?? 'Nuk ka të dhëna për këtë ditë.',
              style: const TextStyle(color: Colors.grey, fontWeight: FontWeight.bold),
            ),
          ],
        ),
      );
    }

    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final isSelectedToday = _selectedDay.year == today.year && _selectedDay.month == today.month && _selectedDay.day == today.day;
    final isSelectedPast = _selectedDay.isBefore(today);

    final displaySlots = slots;
    _scrollToCurrentTime(displaySlots);

    return ListView.builder(
      controller: _scrollController,
      padding: const EdgeInsets.all(16),
      itemCount: displaySlots.length,
      itemBuilder: (context, index) {
        final slot = displaySlots[index];
        final time = (slot['time'] ?? '00:00').toString();
        final isFree = slot['is_free'] == true;

        final parts = time.split(':');
        final slotTime = DateTime(
          _selectedDay.year,
          _selectedDay.month,
          _selectedDay.day,
          int.tryParse(parts[0]) ?? 0,
          int.tryParse(parts.length > 1 ? parts[1] : '0') ?? 0,
        );
        final isPast = isSelectedPast || (isSelectedToday && slotTime.isBefore(now));

        return Padding(
          padding: const EdgeInsets.symmetric(vertical: 6),
          child: Opacity(
            opacity: isPast ? 0.55 : 1.0,
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SizedBox(
                  width: 48,
                  child: Padding(
                    padding: const EdgeInsets.only(top: 14),
                    child: Text(
                      time,
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        color: isPast ? Colors.grey.shade400 : Colors.grey,
                        fontSize: 13,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: isFree
                      ? _buildFreeSlot(time, isPast)
                      : (slot['booking'] != null && (slot['booking'] as Map).isNotEmpty
                          ? ((slot['booking']['status'] ?? '').toString() == 'break'
                              ? _buildBreakCard(slot['booking'])
                              : _buildBookingCard(slot['booking']))
                          : _buildUnavailableSlot(time, slot['message'])),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildUnavailableSlot(String time, String? message) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E212B).withOpacity(0.5) : Colors.grey.shade100,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: Colors.grey.withOpacity(0.3),
          width: 1,
        ),
      ),
      child: Row(
        children: [
          const Icon(Icons.block_rounded, color: Colors.grey, size: 18),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              message ?? bookingTr(context, 'calendar.unavailable'),
              style: const TextStyle(fontWeight: FontWeight.w600, color: Colors.grey, fontSize: 12),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFreeSlot(String time, bool isPast) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return InkWell(
      onTap: () async {
        final dateStr = _formatDate(_selectedDay);
        final appointmentAt = "$dateStr $time:00";

        final res = await Navigator.push(
          context,
          MaterialPageRoute(
            builder: (c) => BookingFormPage(
              item: {
                'appointment_at': appointmentAt,
                'barber_id': _selectedBarberId,
                'source': isPast ? 'walk-in' : 'online',
                'status': isPast ? 'completed' : 'pending',
              },
            ),
          ),
        );
        if (res == true) _refresh();
      },
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: isPast
              ? (isDark ? const Color(0xFF15171F) : Colors.grey.shade100)
              : (isDark ? const Color(0xFF1B1E29) : theme.colorScheme.surfaceContainerHighest.withOpacity(0.3)),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isPast
                ? Colors.grey.withOpacity(0.3)
                : (isDark ? theme.colorScheme.primary.withOpacity(0.4) : theme.colorScheme.outlineVariant),
            width: 1,
          ),
        ),
        child: Row(
          children: [
            Icon(
              isPast ? Icons.history_rounded : Icons.add_circle_outline_rounded,
              color: isPast ? Colors.grey : (isDark ? const Color(0xFF60A5FA) : theme.colorScheme.primary),
              size: 18,
            ),
            const SizedBox(width: 8),
            Text(
              isPast ? bookingTr(context, 'calendar.add_walk_in', {'time': time}) : bookingTr(context, 'calendar.book_at', {'time': time}),
              style: TextStyle(
                fontWeight: FontWeight.w700,
                color: isPast ? Colors.grey.shade600 : (isDark ? const Color(0xFF60A5FA) : theme.colorScheme.primary),
                fontSize: 13,
              ),
            ),
            const Spacer(),
            const Icon(Icons.arrow_forward_ios_rounded, size: 12, color: Colors.grey),
          ],
        ),
      ),
    );
  }

  Widget _buildBreakCard(Map<String, dynamic> breakData) {
    final theme = Theme.of(context);
    final durationMinutes =
        int.tryParse(breakData['duration_minutes']?.toString() ?? '30') ?? 30;
    final appointmentAtStr = breakData['appointment_at']?.toString();

    String timeRangeText = '';
    if (appointmentAtStr != null && appointmentAtStr.isNotEmpty) {
      final clean = appointmentAtStr.replaceAll('T', ' ');
      final parts = clean.split(' ');
      if (parts.length >= 2) {
        final timeParts = parts[1].split(':');
        if (timeParts.length >= 2) {
          final startHour = int.tryParse(timeParts[0]) ?? 0;
          final startMinute = int.tryParse(timeParts[1]) ?? 0;
          final startDt = DateTime(_selectedDay.year, _selectedDay.month, _selectedDay.day, startHour, startMinute);
          final endDt = startDt.add(Duration(minutes: durationMinutes));
          final startFormatted = "${startDt.hour.toString().padLeft(2, '0')}:${startDt.minute.toString().padLeft(2, '0')}";
          final endFormatted = "${endDt.hour.toString().padLeft(2, '0')}:${endDt.minute.toString().padLeft(2, '0')}";
          timeRangeText = "$startFormatted - $endFormatted";
        }
      }
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.amber.withOpacity(0.12),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.amber.withOpacity(0.4), width: 1.2),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              color: Colors.amber.withOpacity(0.2),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.free_breakfast_rounded, color: Colors.amber, size: 18),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Text(
                      'PUSHIM',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w900,
                        color: Colors.amber,
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.amber.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        '${durationMinutes}m',
                        style: const TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                          color: Colors.amber,
                        ),
                      ),
                    ),
                  ],
                ),
                if (timeRangeText.isNotEmpty) ...[
                  const SizedBox(height: 3),
                  Text(
                    timeRangeText,
                    style: TextStyle(
                      fontSize: 11.5,
                      fontWeight: FontWeight.bold,
                      color: Theme.of(context).brightness == Brightness.dark ? Colors.amber.shade200 : Colors.amber.shade900,
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBookingCard(Map<String, dynamic> booking) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    final id = booking['id'];
    final customer = (booking['customer_name'] ?? 'Klient').toString();
    final service = (booking['service_name'] ?? 'Shërbim').toString();
    final barber = (booking['barber_name'] ?? context.staffLabel).toString();
    final status = (booking['status'] ?? 'pending').toString();
    final paymentStatus = (booking['payment_status'] ?? 'unpaid').toString();
    final totalPrice = booking['total_price']?.toString() ?? '0';
    final durationMinutes = int.tryParse(booking['duration_minutes']?.toString() ?? '30') ?? 30;
    final appointmentAtStr = booking['appointment_at']?.toString();

    String timeRangeText = '';
    if (appointmentAtStr != null && appointmentAtStr.isNotEmpty) {
      final clean = appointmentAtStr.replaceAll('T', ' ');
      final parts = clean.split(' ');
      if (parts.length >= 2) {
        final timeParts = parts[1].split(':');
        if (timeParts.length >= 2) {
          final startHour = int.tryParse(timeParts[0]) ?? 0;
          final startMinute = int.tryParse(timeParts[1]) ?? 0;
          final startDt = DateTime(_selectedDay.year, _selectedDay.month, _selectedDay.day, startHour, startMinute);
          final endDt = startDt.add(Duration(minutes: durationMinutes));
          final startFormatted = "${startDt.hour.toString().padLeft(2, '0')}:${startDt.minute.toString().padLeft(2, '0')}";
          final endFormatted = "${endDt.hour.toString().padLeft(2, '0')}:${endDt.minute.toString().padLeft(2, '0')}";
          timeRangeText = "$startFormatted - $endFormatted";
        }
      }
    }

    Color statusColor = Colors.orange;
    if (status == 'completed') statusColor = Colors.green;
    if (status == 'confirmed') statusColor = Colors.blue;
    if (status == 'cancelled') statusColor = Colors.red;

    return InkWell(
      onTap: () async {
        final res = await Navigator.push(
          context,
          MaterialPageRoute(builder: (c) => BookingFormPage(item: booking)),
        );
        if (res == true) _refresh();
      },
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF1E212B) : theme.colorScheme.surface,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isDark ? const Color(0xFF2E3446) : theme.colorScheme.outlineVariant.withOpacity(0.8),
            width: 1.2,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(isDark ? 0.3 : 0.04),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    customer,
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w900,
                      color: isDark ? Colors.white : theme.colorScheme.onSurface,
                    ),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                const SizedBox(width: 8),
                Row(
                  children: [
                    if (paymentStatus == 'paid')
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        margin: const EdgeInsets.only(right: 4),
                        decoration: BoxDecoration(
                          color: Colors.green.withOpacity(0.15),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: const Row(
                          children: [
                            Icon(Icons.check_circle_rounded, size: 10, color: Colors.green),
                            SizedBox(width: 3),
                            Text('PAID', style: TextStyle(color: Colors.green, fontWeight: FontWeight.bold, fontSize: 9)),
                          ],
                        ),
                      ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: statusColor.withOpacity(0.15),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        status.toUpperCase(),
                        style: TextStyle(color: statusColor, fontWeight: FontWeight.bold, fontSize: 9.5),
                      ),
                    ),
                  ],
                ),
              ],
            ),
            if (timeRangeText.isNotEmpty) ...[
              const SizedBox(height: 3),
              Row(
                children: [
                  Icon(Icons.access_time_rounded, size: 12, color: theme.colorScheme.primary),
                  const SizedBox(width: 4),
                  Text(
                    timeRangeText,
                    style: TextStyle(
                      fontSize: 11.5,
                      fontWeight: FontWeight.bold,
                      color: theme.colorScheme.primary,
                    ),
                  ),
                ],
              ),
            ],
            const SizedBox(height: 6),
            Row(
              children: [
                Expanded(
                  child: Text(
                    service,
                    style: TextStyle(
                      color: isDark ? const Color(0xFFF1F5F9) : theme.colorScheme.onSurface,
                      fontWeight: FontWeight.w800,
                      fontSize: 13,
                    ),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                const SizedBox(width: 6),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                  decoration: BoxDecoration(
                    color: theme.colorScheme.surfaceContainerHighest,
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    '${durationMinutes}m',
                    style: TextStyle(fontSize: 10, color: theme.colorScheme.onSurfaceVariant, fontWeight: FontWeight.bold),
                  ),
                ),
                const SizedBox(width: 6),
                Text(
                  '($totalPrice Lekë)',
                  style: TextStyle(color: isDark ? Colors.grey.shade400 : Colors.black87, fontSize: 11.5, fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: 4),
            Row(
              children: [
                Icon(Icons.person_outline_rounded, size: 12, color: isDark ? Colors.grey.shade400 : Colors.grey.shade600),
                const SizedBox(width: 4),
                Text(
                  barber,
                  style: TextStyle(color: isDark ? Colors.grey.shade400 : Colors.grey.shade600, fontSize: 11.5),
                ),
              ],
            ),
            if ((booking['sms_messages'] as List? ?? []).isNotEmpty) ...[
              Builder(builder: (context) {
                final rawSmsList = (booking['sms_messages'] as List? ?? []);
                final Map<String, Map<String, dynamic>> uniqueSmsMap = {};
                for (final msg in rawSmsList) {
                  final type = (msg['type'] ?? 'confirmation').toString();
                  uniqueSmsMap[type] = Map<String, dynamic>.from(msg);
                }
                final displaySms = uniqueSmsMap.values.toList();

                return Padding(
                  padding: const EdgeInsets.only(top: 6),
                  child: Wrap(
                    spacing: 6,
                    runSpacing: 4,
                    children: displaySms.map((msg) {
                      final typeLabel = (msg['type_label'] ?? 'SMS').toString();
                      final status = (msg['status'] ?? 'pending').toString().toLowerCase();

                      Color color = Colors.amber.shade700;
                      IconData icon = Icons.access_time_rounded;
                      if (status == 'sent') {
                        color = Colors.green.shade600;
                        icon = Icons.check_circle_rounded;
                      } else if (status == 'failed') {
                        color = Colors.red.shade600;
                        icon = Icons.cancel_rounded;
                      }

                      return Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: color.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(color: color.withOpacity(0.3), width: 1),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(icon, size: 10, color: color),
                            const SizedBox(width: 3),
                            Text(
                              '$typeLabel: ${status.toUpperCase()}',
                              style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: color),
                            ),
                          ],
                        ),
                      );
                    }).toList(),
                  ),
                );
              }),
            ],
            if (paymentStatus != 'paid' && status != 'cancelled') ...[
              const SizedBox(height: 8),
              SizedBox(
                width: double.infinity,
                height: 32,
                child: OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                    foregroundColor: Colors.green,
                    side: const BorderSide(color: Colors.green, width: 1.2),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    padding: EdgeInsets.zero,
                  ),
                  onPressed: () async {
                    final res = await Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (c) => PaymentFormPage(
                          item: {
                            'booking_id': id,
                            'customer_id': booking['customer_id'],
                            'amount': totalPrice,
                            'status': 'completed',
                            'payment_method': 'cash',
                          },
                        ),
                      ),
                    );
                    if (res == true) _refresh();
                  },
                  icon: const Icon(Icons.check_circle_outline_rounded, size: 15),
                  label: const Text('Kryej & Regjistro Pagesën', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
