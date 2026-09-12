import 'dart:convert';
import 'package:pusher_reverb_flutter/pusher_reverb_flutter.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'realtime_config.dart';

class RealtimeService {
  RealtimeService._();
  static final instance = RealtimeService._();

  ReverbClient? _client;
  ReverbClient? get client => _client;
  final Set<String> _subscribed = {};
  final Map<String, List<void Function(String action, Map<String, dynamic> data)>> _listeners = {};

  Future<void> start() async {
    if (_client != null) return;
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');
    if (token == null || token.isEmpty || RealtimeConfig.appKey == 'CHANGE_ME') return;

    Future<Map<String, String>> authorizer(String channelName, String socketId) async => {
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
    };

    _client = ReverbClient.instance(
      host: RealtimeConfig.host,
      port: RealtimeConfig.port,
      appKey: RealtimeConfig.appKey,
      useTLS: RealtimeConfig.useTls,
      authEndpoint: RealtimeConfig.authEndpoint,
      authorizer: authorizer,
    );
    await _client!.connect();
  }

  Future<void> subscribe(String resource, void Function(String action, Map<String, dynamic> data) listener) async {
    _listeners.putIfAbsent(resource, () => []).add(listener);
    await start();
    if (_client == null || _subscribed.contains(resource)) return;

    final channel = _client!.subscribeToPrivateChannel('private-mobile.$resource');
    channel.bind('$resource.changed', (eventName, eventData) {
      final data = eventData is String ? jsonDecode(eventData) : eventData;
      if (data is! Map) return;
      final action = data['action']?.toString();
      final record = Map<String, dynamic>.from(data['data'] is Map ? data['data'] as Map : {});
      if (action == null) return;
      for (final callback in List.of(_listeners[resource] ?? const [])) {
        callback(action, record);
      }
    });
    _subscribed.add(resource);
  }

  Future<void> stop() async {
    for (final resource in _subscribed) {
      _client?.unsubscribeFromChannel('private-mobile.$resource');
    }
    _subscribed.clear();
    _listeners.clear();
    _client?.disconnect();
    _client = null;
  }
}