import 'dart:convert';
import '../../../../services/api_service.dart';

class NotificationRepository {
  Future<Map<String, dynamic>> getSettings() async {
    final res = await ApiService.get('/notifications/settings');
    return jsonDecode(res.body);
  }

  Future<bool> toggleModule(String moduleName) async {
    final res = await ApiService.post('/notifications/toggle-module', {
      'module': moduleName,
    });
    final data = jsonDecode(res.body);
    return data['enabled'] ?? false;
  }

  Future<bool> toggleEvent(int eventId) async {
    final res = await ApiService.post('/notifications/toggle-event', {
      'event_id': eventId,
    });
    final data = jsonDecode(res.body);
    return data['enabled'] ?? false;
  }
}
