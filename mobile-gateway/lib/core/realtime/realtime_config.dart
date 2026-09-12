class RealtimeConfig {
  static const host = String.fromEnvironment('REVERB_HOST', defaultValue: '10.10.12.14');
  static const port = int.fromEnvironment('REVERB_PORT', defaultValue: 8080);
  static const appKey = String.fromEnvironment('REVERB_APP_KEY', defaultValue: 'laraauto_key_6789');
  static const apiBaseUrl = String.fromEnvironment('API_BASE_URL', defaultValue: 'http://10.10.12.14:5000');
  static const useTls = bool.fromEnvironment('REVERB_TLS', defaultValue: false);
  static String get authEndpoint => '${apiBaseUrl}/broadcasting/auth';
}
