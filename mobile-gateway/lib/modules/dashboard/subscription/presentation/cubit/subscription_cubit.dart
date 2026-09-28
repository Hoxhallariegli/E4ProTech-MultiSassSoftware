import 'dart:async';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../data/subscription_repository.dart';
import 'subscription_state.dart';

class SubscriptionCubit extends Cubit<SubscriptionState> {
  final SubscriptionRepository repository;
  final List<Map<String, dynamic>> _items = [];
  Timer? _searchDebounce;
  int _page = 1;
  bool _hasMore = true;
  String _search = '';
  String? _sort;
  String _direction = 'desc';
  Map<String, dynamic> _filters = {};

  SubscriptionCubit(this.repository) : super(const SubscriptionInitial());

  List<Map<String, dynamic>> get items => List.unmodifiable(_items);

  Future<void> load({bool refresh = false, String? search, Map<String, dynamic>? filters}) async {
    if (search != null) _search = search;
    if (filters != null) _filters = Map<String, dynamic>.from(filters);
    if (refresh) { _page = 1; _hasMore = true; _items.clear(); }
    if (_page == 1) emit(SubscriptionLoading(List.unmodifiable(_items)));

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
      emit(SubscriptionLoaded(List.unmodifiable(_items), hasMore: _hasMore));
    } catch (e) {
      emit(SubscriptionFailure(_cleanError(e), List.unmodifiable(_items)));
    }
  }

  void search(String value) {
    _searchDebounce?.cancel();
    _searchDebounce = Timer(const Duration(milliseconds: 350), () {
      load(refresh: true, search: value);
    });
  }

  Future<void> loadMore() async {
    if (!_hasMore || state is SubscriptionLoading) return;
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
    emit(const SubscriptionSaving());
    try {
      final item = await repository.save(payload, id: id, files: files);
      emit(SubscriptionSaved(item));
    } catch (e) {
      emit(SubscriptionFailure(_cleanError(e), List.unmodifiable(_items)));
    }
  }

  Future<void> delete(dynamic id) async {
    emit(SubscriptionDeleting(id));
    try {
      await repository.delete(id);
      _items.removeWhere((item) => item['id'].toString() == id.toString());
      emit(SubscriptionLoaded(List.unmodifiable(_items), hasMore: _hasMore));
    } catch (e) {
      emit(SubscriptionFailure(_cleanError(e), List.unmodifiable(_items)));
    }
  }

  String _cleanError(Object error) {
    final text = error.toString();
    return text.startsWith('Exception: ') ? text.substring(11) : text;
  }

  void handleRealtime(String action, Map<String, dynamic> data) {
    final id = data['id']?.toString();
    if (action == 'deleted') {
      if (id != null) _items.removeWhere((item) => item['id']?.toString() == id);
      emit(SubscriptionLoaded(List.unmodifiable(_items), hasMore: _hasMore));
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
    emit(SubscriptionLoaded(List.unmodifiable(_items), hasMore: _hasMore));
  }

  @override
  Future<void> close() {
    _searchDebounce?.cancel();
    return super.close();
  }
}