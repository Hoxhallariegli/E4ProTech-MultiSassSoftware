sealed class BookingState {
  const BookingState();
}

final class BookingInitial extends BookingState {
  const BookingInitial();
}

final class BookingLoading extends BookingState {
  final List<Map<String, dynamic>> items;
  const BookingLoading([this.items = const []]);
}

final class BookingLoaded extends BookingState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const BookingLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class BookingSaving extends BookingState {
  const BookingSaving();
}

final class BookingSaved extends BookingState {
  final Map<String, dynamic> item;
  const BookingSaved(this.item);
}

final class BookingDeleting extends BookingState {
  final int id;
  const BookingDeleting(this.id);
}

final class BookingFailure extends BookingState {
  final String message;
  final List<Map<String, dynamic>> items;
  const BookingFailure(this.message, [this.items = const []]);
}

final class BookingCalendarLoaded extends BookingState {
  final List<Map<String, dynamic>> barbers;
  final int? selectedBarberId;
  final Map<String, int> calendarStats;
  final List<dynamic> daySlots;
  final String? calendarMessage;
  final bool isLoading;

  const BookingCalendarLoaded({
    required this.barbers,
    this.selectedBarberId,
    required this.calendarStats,
    required this.daySlots,
    this.calendarMessage,
    this.isLoading = false,
  });

  BookingCalendarLoaded copyWith({
    List<Map<String, dynamic>>? barbers,
    int? selectedBarberId,
    Map<String, int>? calendarStats,
    List<dynamic>? daySlots,
    String? calendarMessage,
    bool? isLoading,
  }) {
    return BookingCalendarLoaded(
      barbers: barbers ?? this.barbers,
      selectedBarberId: selectedBarberId ?? this.selectedBarberId,
      calendarStats: calendarStats ?? this.calendarStats,
      daySlots: daySlots ?? this.daySlots,
      calendarMessage: calendarMessage ?? this.calendarMessage,
      isLoading: isLoading ?? this.isLoading,
    );
  }
}
