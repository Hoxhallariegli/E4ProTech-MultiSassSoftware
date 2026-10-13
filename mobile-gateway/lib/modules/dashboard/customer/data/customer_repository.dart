import 'dart:convert';
import '../../../../services/api_service.dart';

class CustomerRepository {
  static const _perPage = 25;
  static final Map<String, ({List<Map<String, dynamic>> data, DateTime timestamp})> _lookupCache = {};
  static final Map<String, ({CustomerPage page, Map<String, dynamic> meta, DateTime timestamp})> _indexCache = {};

  static void clearCache() {
    _lookupCache.clear();
    _indexCache.clear();
  }

  Future<({CustomerPage page, Map<String, dynamic> meta})> index({
    int page = 1,
    String search = '',
    String? sort,
    String direction = 'desc',
    Map<String, dynamic> filters = const {},
    bool forceRefresh = false,
  }) async {
    final cacheKey = '$page:$search:$sort:$direction:${filters.toString()}';
    final cached = _indexCache[cacheKey];

    if (!forceRefresh && cached != null && DateTime.now().difference(cached.timestamp).inMinutes < 3) {
      return (page: cached.page, meta: cached.meta);
    }

    final query = <String, String>{
      'page': page.toString(),
      'per_page': _perPage.toString(),
      if (search.trim().isNotEmpty) 'search': search.trim(),
      if (sort != null && sort.isNotEmpty) 'sort': sort,
      'direction': direction,
    };
    filters.forEach((key, value) {
      if (value != null && value.toString().isNotEmpty) query[key] = value.toString();
    });

    final res = await ApiService.get('/customers?' + Uri(queryParameters: query).query);
    _ensureSuccess(res);
    final decoded = Map<String, dynamic>.from(jsonDecode(res.body));
    final data = (decoded['data'] as List? ?? [])
        .map((e) => Map<String, dynamic>.from(e as Map))
        .toList();
    final meta = Map<String, dynamic>.from(decoded['meta'] ?? {});
    final result = (page: CustomerPage(data), meta: meta);

    _indexCache[cacheKey] = (page: result.page, meta: result.meta, timestamp: DateTime.now());
    return result;
  }

  Future<Map<String, dynamic>> show(dynamic id) async {
    final res = await ApiService.get('/customers/$id');
    _ensureSuccess(res);
    return Map<String, dynamic>.from(jsonDecode(res.body)['data'] ?? {});
  }

  Future<Map<String, dynamic>> save(Map<String, dynamic> payload, {dynamic id, Map<String, String>? files}) async {
    final multipartPayload = Map<String, dynamic>.from(payload);
    if (id != null) multipartPayload['_method'] = 'PUT';
    final res = (files != null && files.isNotEmpty)
        ? await ApiService.postMultipart(
            id == null ? '/customers' : '/customers/$id',
            multipartPayload,
            files: files,
          )
        : id == null
            ? await ApiService.post('/customers', payload)
            : await ApiService.put('/customers/$id', payload);
    _ensureSuccess(res);
    clearCache();
    return Map<String, dynamic>.from(jsonDecode(res.body)['data'] ?? {});
  }

  Future<void> delete(dynamic id) async {
    final res = await ApiService.delete('/customers/$id');
    _ensureSuccess(res);
    clearCache();
  }

  Future<List<Map<String, dynamic>>> lookup(String endpoint, {String search = '', bool forceRefresh = false}) async {
    final cacheKey = '$endpoint:$search';
    final cached = _lookupCache[cacheKey];

    if (!forceRefresh && cached != null && DateTime.now().difference(cached.timestamp).inMinutes < 5) {
      return cached.data;
    }

    final query = search.trim().isEmpty ? '' : '?search=${Uri.encodeQueryComponent(search.trim())}&per_page=50';
    final res = await ApiService.get('/$endpoint$query');
    _ensureSuccess(res);
    final decoded = jsonDecode(res.body);
    final data = decoded is Map ? decoded['data'] : decoded;
    final result = (data as List? ?? []).map((e) => Map<String, dynamic>.from(e as Map)).toList();

    _lookupCache[cacheKey] = (data: result, timestamp: DateTime.now());
    return result;
  }

  void _ensureSuccess(dynamic res) {
    if (res.statusCode < 200 || res.statusCode >= 300) {
      throw Exception(ApiService.extractErrorMessage(res));
    }
  }
}

class CustomerPage {
  final List<Map<String, dynamic>> items;
  const CustomerPage(this.items);
}
