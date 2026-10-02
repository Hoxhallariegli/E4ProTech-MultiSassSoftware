import 'package:flutter/foundation.dart';

class RealtimeService {
  RealtimeService._();
  static final instance = RealtimeService._();

  final Map<String, List<void Function(String action, Map<String, dynamic> data)>> _listeners = {};

  Future<void> start() async {
    debugPrint('⚡ [RealtimeService] Pure FCM Realtime Service initialized.');
  }

  void notifyListeners(String resource, String action, Map<String, dynamic> data) {
    debugPrint('⚡ [RealtimeService] Notifying listeners for [$resource] Action: [$action]');
    final callbacks = _listeners[resource];
    if (callbacks != null) {
      for (final callback in List.of(callbacks)) {
        try {
          callback(action, data);
        } catch (e) {
          debugPrint('Error in realtime listener callback: $e');
        }
      }
    }
  }

  Future<void> subscribe(String resource, void Function(String action, Map<String, dynamic> data) listener, {dynamic tenantId}) async {
    _listeners.putIfAbsent(resource, () => []).add(listener);
  }

  Future<void> stop() async {
    _listeners.clear();
  }
}
