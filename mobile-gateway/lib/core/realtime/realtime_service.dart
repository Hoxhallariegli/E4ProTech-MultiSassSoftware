import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

class RealtimeService {
  RealtimeService._();
  static final instance = RealtimeService._();

  final Set<String> _subscribed = {};
  final Map<String, List<void Function(String action, Map<String, dynamic> data)>> _listeners = {};

  Future<void> start() async {
    // FCM-based Realtime UI Updates
  }

  Future<void> subscribe(String resource, void Function(String action, Map<String, dynamic> data) listener, {dynamic tenantId}) async {
    _listeners.putIfAbsent(resource, () => []).add(listener);
  }

  void notifyListeners(String resource, String action, Map<String, dynamic> data) {
    final callbacks = List<void Function(String action, Map<String, dynamic> data)>.from(_listeners[resource] ?? []);
    for (final callback in callbacks) {
      try {
        callback(action, data);
      } catch (e) {
        debugPrint('Error in RealtimeService listener callback: $e');
      }
    }
  }

  Future<void> stop() async {
    _subscribed.clear();
    _listeners.clear();
  }
}
