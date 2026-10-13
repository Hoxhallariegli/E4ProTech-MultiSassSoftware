class RealtimeConfig {
  static const host = 'app.e4protech.com';
  static const port = 443;
  static const appKey = 'laraauto_key_6789';
  static const apiBaseUrl = 'https://app.e4protech.com';
  static const useTls = true;

  static String get authEndpoint => '$apiBaseUrl/broadcasting/auth';
}
