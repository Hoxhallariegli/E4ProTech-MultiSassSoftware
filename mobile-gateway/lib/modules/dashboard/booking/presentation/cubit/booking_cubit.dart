import 'dart:async';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mobile_gateway/services/api_service.dart';
import '../../data/booking_repository.dart';
import 'booking_state.dart';

class BookingCubit extends Cubit<BookingState> {
  final BookingRepository repository;
  final List<Map<String, dynamic>> _items = [];
  Timer? _searchDebounce;
  int _page = 1;
  bool _hasMore = true;
  String _search = '';
  String? _sort;
  String _direction = 'desc';
  Map<String, dynamic> _filters = {};

  BookingCubit(this.repository) : super(const BookingInitial());

  List<Map<String, dynamic>> get items => List.unmodifiable(_items);

  Future<void> load({bool refresh = false, String? search, Map<String, dynamic>? filters}) async {
    if (search != null) _search = search;
    if (filters != null) _filters = Map<String, dynamic>.from(filters);
    if (refresh) { _page = 1; _hasMore = true; _items.clear(); }
    if (_page == 1) emit(BookingLoading(List.unmodifiable(_items)));

    try {
      final result = await repository.index(
        page: _page,
        search: _search,
        sort: _sort,
        direction: _direction,
        filters: _filters,
      );
      if (_page == 1) _items.clear();
      _items.addAll(result.page.items);
      final current = int.tryParse(result.meta['current_page']?.toString() ?? '') ?? _page;
      final last = int.tryParse(result.meta['last_page']?.toString() ?? '') ?? current;
      _hasMore = current < last || result.meta['next_page_url'] != null;
      emit(BookingLoaded(List.unmodifiable(_items), hasMore: _hasMore));
    } catch (e) {
      emit(BookingFailure(_cleanError(e), List.unmodifiable(_items)));
    }
  }

  void search(String value) {
    _searchDebounce?.cancel();
    _searchDebounce = Timer(const Duration(milliseconds: 350), () {
      load(refresh: true, search: value);
    });
  }

  Future<void> loadMore() async {
    if (!_hasMore || state is BookingLoading) return;
    _page++;
    await load();
  }

  Future<void> refresh() => load(refresh: true);

  void setSort(String field) {
    if (_sort == field) {
      _direction = _direction == 'asc' ? 'desc' : 'asc';
    } else {
      _sort = field;
      _direction = 'asc';
    }
    load(refresh: true);
  }

  Future<void> save(Map<String, dynamic> payload, {dynamic id, Map<String, String>? files}) async {
    emit(const BookingSaving());
    try {
      final item = await repository.save(payload, id: id, files: files);
      emit(BookingSaved(item));
    } catch (e) {
      emit(BookingFailure(_cleanError(e), List.unmodifiable(_items)));
    }
  }

  Future<void> delete(dynamic id) async {
    emit(BookingDeleting(id));
    try {
      await repository.delete(id);
      _items.removeWhere((item) => item['id'].toString() == id.toString());
      emit(BookingLoaded(List.unmodifiable(_items), hasMore: _hasMore));
    } catch (e) {
      emit(BookingFailure(_cleanError(e), List.unmodifiable(_items)));
    }
  }

  String _cleanError(Object error) {
    return ApiService.extractErrorMessage(error);
  }

  void handleRealtime(String action, Map<String, dynamic> data) {
    final id = data['id']?.toString();
    if (action == 'deleted') {
      if (id != null) _items.removeWhere((item) => item['id']?.toString() == id);
      emit(BookingLoaded(List.unmodifiable(_items), hasMore: _hasMore));
      return;
    }
    if (id == null) return;
    final index = _items.indexWhere((item) => item['id']?.toString() == id);
    if (action == 'created' && index == -1) {
      _items.insert(0, data);
    } else if (action == 'updated' && index != -1) {
      _items[index] = data;
    } else if (action == 'updated' && index == -1) {
      _items.insert(0, data);
    }
    emit(BookingLoaded(List.unmodifiable(_items), hasMore: _hasMore));
  }

  List<Map<String, dynamic>> _cachedBarbers = [];
  Map<String, int> _cachedStats = {};
  List<dynamic> _cachedSlots = [];
  bool _isCalendarLoading = false;

  List<Map<String, dynamic>> get barbers => List.unmodifiable(_cachedBarbers);

  Future<void> loadInitialData() async {
    await refreshCalendarData(
      month: DateTime.now(),
      day: DateTime.now(),
      reloadBarbersList: true,
    );
  }

  Future<void> refreshCalendarData({
    required DateTime month,
    required DateTime day,
    int? preferredBarberId,
    bool reloadBarbersList = true,
  }) async {
    _isCalendarLoading = true;

    if (reloadBarbersList || _cachedBarbers.isEmpty) {
      _cachedStats = {};
      _cachedSlots = [];
      try {
        _cachedBarbers = await repository.lookup('barbers');
      } catch (_) {
        _cachedBarbers = [];
      }
    }

    int? activeBarberId = preferredBarberId;
    if (_cachedBarbers.isNotEmpty) {
      final validIds = _cachedBarbers
          .map((b) => int.tryParse(b['id']?.toString() ?? ''))
          .whereType<int>()
          .toList();

      if (activeBarberId == null || !validIds.contains(activeBarberId)) {
        activeBarberId = validIds.isNotEmpty ? validIds.first : null;
      }
    } else {
      activeBarberId = null;
    }

    emit(BookingCalendarLoaded(
      barbers: _cachedBarbers,
      selectedBarberId: activeBarberId,
      calendarStats: _cachedStats,
      daySlots: _cachedSlots,
      isLoading: true,
    ));

    try {
      if (activeBarberId != null) {
        final stats = await repository.getMonthStats(
          month: month.month,
          year: month.year,
          barberId: activeBarberId,
        );
        _cachedStats = stats;

        final dateStr = "${day.year}-${day.month.toString().padLeft(2, '0')}-${day.day.toString().padLeft(2, '0')}";
        final schedule = await repository.getDaySchedule(
          date: dateStr,
          barberId: activeBarberId,
        );
        _cachedSlots = schedule['daySlots'] ?? [];
      } else {
        _cachedStats = {};
        _cachedSlots = [];
      }

      emit(BookingCalendarLoaded(
        barbers: _cachedBarbers,
        selectedBarberId: activeBarberId,
        calendarStats: _cachedStats,
        daySlots: _cachedSlots,
        isLoading: false,
      ));
    } catch (e) {
      emit(BookingCalendarLoaded(
        barbers: _cachedBarbers,
        selectedBarberId: activeBarberId,
        calendarStats: _cachedStats,
        daySlots: _cachedSlots,
        calendarMessage: e.toString(),
        isLoading: false,
      ));
    } finally {
      _isCalendarLoading = false;
    }
  }

  Future<void> loadCalendarData({
    required DateTime month,
    required DateTime day,
    int? barberId,
    bool reloadBarbersList = false,
  }) async {
    await refreshCalendarData(
      month: month,
      day: day,
      preferredBarberId: barberId,
      reloadBarbersList: reloadBarbersList,
    );
  }

  Future<void> fetchDaySlots({required DateTime day, int? barberId}) async {
    await refreshCalendarData(
      month: day,
      day: day,
      preferredBarberId: barberId,
      reloadBarbersList: false,
    );
  }

  @override
  void emit(BookingState state) {
    if (isClosed) return;
    super.emit(state);
  }

  @override
  Future<void> close() {
    _searchDebounce?.cancel();
    return super.close();
  }
}
