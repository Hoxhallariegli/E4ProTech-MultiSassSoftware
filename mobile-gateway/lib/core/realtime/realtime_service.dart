import 'dart:async';
import 'package:flutter/foundation.dart';

class RealtimeService {
  RealtimeService._();
  static final instance = RealtimeService._();

  final Map<String, List<void Function(String action, Map<String, dynamic> data)>> _listeners = {};

  Future<void> start() async {
    // Pure Firebase FCM Realtime mode active. Reverb WebSocket disabled.
    return;
  }

  Future<void> subscribe(String resource, void Function(String action, Map<String, dynamic> data) listener, {dynamic tenantId}) async {
    _listeners.putIfAbsent(resource, () => []).add(listener);
  }

  void notifyListeners(String resource, String action, Map<String, dynamic> data) {
    final callbacks = _listeners[resource];
    if (callbacks == null || callbacks.isEmpty) return;
    for (final callback in List.of(callbacks)) {
      try {
        callback(action, data);
      } catch (e) {
        debugPrint('Error notifying listener for $resource: $e');
      }
    }
  }

  Future<void> stop() async {
    _listeners.clear();
  }
}
