import 'dart:async';

class RealtimeService {
  RealtimeService._();
  static final instance = RealtimeService._();

  final Map<String, List<void Function(String action, Map<String, dynamic> data)>> _listeners = {};

  Future<void> start() async {
    // Firebase FCM Event Bus - No WebSocket connection needed
  }

  Future<void> subscribe(
    String resource,
    void Function(String action, Map<String, dynamic> data) listener, {
    dynamic tenantId,
  }) async {
    _listeners.putIfAbsent(resource, () => []).add(listener);
  }

  void notifyListeners(String resource, String action, Map<String, dynamic> data) {
    final callbacks = List<void Function(String, Map<String, dynamic>)>.from(_listeners[resource] ?? const []);
    for (final callback in callbacks) {
      try {
        callback(action, data);
      } catch (_) {}
    }
  }

  Future<void> stop() async {
    _listeners.clear();
  }
}
