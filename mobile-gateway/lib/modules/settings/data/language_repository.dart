import 'dart:convert';
import '../../../../services/api_service.dart';

class LanguageRepository {
  Future<List<Map<String, dynamic>>> getLanguages() async {
    try {
      final res = await ApiService.get('/languages');
      final decoded = jsonDecode(res.body);
      return List<Map<String, dynamic>>.from(decoded['data'] ?? []);
    } catch (e) {
      // Fallback baseline
      return [
        {'code': 'en', 'name': 'English'},
        {'code': 'sq', 'name': 'Shqip'},
      ];
    }
  }
}
