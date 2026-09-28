import 'dart:convert';
import '../../../../services/api_service.dart';

class BookingRepository {
  static const _perPage = 25;

  Future<({BookingPage page, Map<String, dynamic> meta})> index({
    int page = 1,
    String search = '',
    String? sort,
    String direction = 'desc',
    Map<String, dynamic> filters = const {},
  }) async {
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

    final res = await ApiService.get('/bookings?' + Uri(queryParameters: query).query);
    _ensureSuccess(res);
    final decoded = Map<String, dynamic>.from(jsonDecode(res.body));
    final data = (decoded['data'] as List? ?? [])
        .map((e) => Map<String, dynamic>.from(e as Map))
        .toList();
    final meta = Map<String, dynamic>.from(decoded['meta'] ?? {});
    return (page: BookingPage(data), meta: meta);
  }

  Future<Map<String, dynamic>> show(dynamic id) async {
    final res = await ApiService.get('/bookings/$id');
    _ensureSuccess(res);
    return Map<String, dynamic>.from(jsonDecode(res.body)['data'] ?? {});
  }

  Future<Map<String, dynamic>> save(Map<String, dynamic> payload, {dynamic id, Map<String, String>? files}) async {
    final multipartPayload = Map<String, dynamic>.from(payload);
    if (id != null) multipartPayload['_method'] = 'PUT';
    final res = (files != null && files.isNotEmpty)
        ? await ApiService.postMultipart(
            id == null ? '/bookings' : '/bookings/$id',
            multipartPayload,
            files: files,
          )
        : id == null
            ? await ApiService.post('/bookings', payload)
            : await ApiService.put('/bookings/$id', payload);
    _ensureSuccess(res);
    return Map<String, dynamic>.from(jsonDecode(res.body)['data'] ?? {});
  }

  Future<void> delete(dynamic id) async {
    final res = await ApiService.delete('/bookings/$id');
    _ensureSuccess(res);
  }

  Future<List<Map<String, dynamic>>> lookup(String endpoint, {String search = ''}) async {
    final query = search.trim().isEmpty ? '' : '?search=${Uri.encodeQueryComponent(search.trim())}&per_page=50';
    final res = await ApiService.get('/$endpoint$query');
    _ensureSuccess(res);
    final decoded = jsonDecode(res.body);
    final data = decoded is Map ? decoded['data'] : decoded;
    return (data as List? ?? []).map((e) => Map<String, dynamic>.from(e as Map)).toList();
  }

  Future<Map<String, int>> getMonthStats({required int month, required int year, int? barberId}) async {
    String url = '/bookings/calendar?month=$month&year=$year';
    if (barberId != null) url += '&barber_id=$barberId';

    final res = await ApiService.get(url);
    _ensureSuccess(res);
    final raw = jsonDecode(res.body)['data'];
    final Map<String, dynamic> data = (raw is Map) ? Map<String, dynamic>.from(raw) : {};
    return data.map((key, value) => MapEntry(key.toString(), int.tryParse(value.toString()) ?? 0));
  }

  Future<Map<String, dynamic>> getDaySchedule({required String date, int? barberId}) async {
    String url = '/bookings/day-schedule?date=$date';
    if (barberId != null) url += '&barber_id=$barberId';

    final res = await ApiService.get(url);
    _ensureSuccess(res);
    return Map<String, dynamic>.from(jsonDecode(res.body));
  }

  void _ensureSuccess(dynamic res) {
    if (res.statusCode < 200 || res.statusCode >= 300) {
      throw Exception(ApiService.extractErrorMessage(res));
    }
  }
}

class BookingPage {
  final List<Map<String, dynamic>> items;
  const BookingPage(this.items);
}
