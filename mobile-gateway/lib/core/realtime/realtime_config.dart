class RealtimeConfig {
  static const host = '10.10.12.14';
  static const port = 8080;
  static const appKey = 'laraauto_key_6789';
  static const apiBaseUrl = 'http://10.10.12.14:5000';
  static const useTls = false;

  static String get authEndpoint => '$apiBaseUrl/broadcasting/auth';
}
