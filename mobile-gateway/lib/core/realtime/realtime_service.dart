class RealtimeService {
  RealtimeService._();
  static final instance = RealtimeService._();

  final Map<String, List<void Function(String action, Map<String, dynamic> data)>> _listeners = {};

  Future<void> start() async {
    // Pure Firebase FCM Realtime Event Dispatcher
  }

  Future<void> subscribe(String resource, void Function(String action, Map<String, dynamic> data) listener, {dynamic tenantId}) async {
    _listeners.putIfAbsent(resource, () => []).add(listener);
  }

  void notifyListeners(String resource, String action, Map<String, dynamic> data) {
    final callbacks = List.of(_listeners[resource] ?? const []);
    for (final callback in callbacks) {
      callback(action, data);
    }
  }

  Future<void> stop() async {
    _listeners.clear();
  }
}
