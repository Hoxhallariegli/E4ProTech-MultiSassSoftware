import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../main.dart';
import '../modules/auth/presentation/pages/login_page.dart';

class ApiService {
  static String? _customUrl;
  static final ValueNotifier<bool> isOffline = ValueNotifier<bool>(false);
  static final ValueNotifier<bool> isOnlineRestored = ValueNotifier<bool>(false);
  static Timer? _recoveryTimer;

  static void initAutoHealthCheck() {
    isOffline.addListener(() {
      if (isOffline.value) {
        _startRecoveryTimer();
      } else {
        _stopRecoveryTimer();
      }
    });
  }

  static void _startRecoveryTimer() {
    _recoveryTimer?.cancel();
    _recoveryTimer = Timer.periodic(const Duration(seconds: 15), (_) async {
      await checkServerHealth();
    });
  }

  static void _stopRecoveryTimer() {
    _recoveryTimer?.cancel();
    _recoveryTimer = null;
  }

  static Future<void> checkServerHealth() async {
    try {
      final baseUrl = await serverUrl;
      final uri = Uri.parse('$baseUrl$apiVersion/status');
      final res = await http.get(uri).timeout(const Duration(seconds: 5));

      if (res.statusCode >= 200 && res.statusCode < 400) {
        if (isOffline.value) {
          isOffline.value = false;
          isOnlineRestored.value = true;
          _stopRecoveryTimer();
          Future.delayed(const Duration(seconds: 4), () {
            isOnlineRestored.value = false;
          });
        }
      }
    } catch (_) {
      if (!isOffline.value) {
        isOffline.value = true;
      }
    }
  }

  static Future<String> get serverUrl async {
    if (_customUrl != null) return _customUrl!;
    final prefs = await SharedPreferences.getInstance();
    final savedUrl = prefs.getString('custom_api_base_url');
    if (savedUrl != null && savedUrl.isNotEmpty) {
      _customUrl = savedUrl;
      return savedUrl;
    }
    return const String.fromEnvironment('API_BASE_URL', defaultValue: 'https://app.e4protech.com');
  }

  static Future<void> setCustomBaseUrl(String url) async {
    final prefs = await SharedPreferences.getInstance();
    if (url.isEmpty) {
      await prefs.remove('custom_api_base_url');
      _customUrl = null;
    } else {
      String formattedUrl = url.trim();
      if (!formattedUrl.startsWith('http://') && !formattedUrl.startsWith('https://')) {
        formattedUrl = 'http://$formattedUrl';
      }
      if (formattedUrl.endsWith('/')) {
        formattedUrl = formattedUrl.substring(0, formattedUrl.length - 1);
      }
      await prefs.setString('custom_api_base_url', formattedUrl);
      _customUrl = formattedUrl;
    }
  }

  static const String apiVersion = '/api/mobile';

  static Future<Map<String, String>> _getHeaders() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null && token.isNotEmpty) 'Authorization': 'Bearer $token',
    };
  }

  static Future<http.Response> get(String url) async {
    try {
      final baseUrl = await serverUrl;
      final uri = Uri.parse('$baseUrl$apiVersion$url');
      final headers = await _getHeaders();
      final res = await http.get(uri, headers: headers);
      if (isOffline.value) {
        isOffline.value = false;
        isOnlineRestored.value = true;
        _stopRecoveryTimer();
        Future.delayed(const Duration(seconds: 4), () {
          isOnlineRestored.value = false;
        });
      }
      _ensureSuccess(res);
      return res;
    } catch (e) {
      _handleNetworkError(e);
      rethrow;
    }
  }

  static Future<http.Response> post(String url, Map<String, dynamic> body) async {
    try {
      final baseUrl = await serverUrl;
      final uri = Uri.parse('$baseUrl$apiVersion$url');
      final headers = await _getHeaders();
      final res = await http.post(uri, headers: headers, body: jsonEncode(body));
      if (isOffline.value) {
        isOffline.value = false;
        isOnlineRestored.value = true;
        _stopRecoveryTimer();
        Future.delayed(const Duration(seconds: 4), () {
          isOnlineRestored.value = false;
        });
      }
      _ensureSuccess(res);
      return res;
    } catch (e) {
      _handleNetworkError(e);
      rethrow;
    }
  }

  static Future<http.Response> put(String url, Map<String, dynamic> body) async {
    try {
      final baseUrl = await serverUrl;
      final uri = Uri.parse('$baseUrl$apiVersion$url');
      final headers = await _getHeaders();
      final res = await http.put(uri, headers: headers, body: jsonEncode(body));
      if (isOffline.value) {
        isOffline.value = false;
        isOnlineRestored.value = true;
        _stopRecoveryTimer();
        Future.delayed(const Duration(seconds: 4), () {
          isOnlineRestored.value = false;
        });
      }
      _ensureSuccess(res);
      return res;
    } catch (e) {
      _handleNetworkError(e);
      rethrow;
    }
  }

  static Future<http.Response> delete(String url) async {
    try {
      final baseUrl = await serverUrl;
      final uri = Uri.parse('$baseUrl$apiVersion$url');
      final headers = await _getHeaders();
      final res = await http.delete(uri, headers: headers);
      if (isOffline.value) {
        isOffline.value = false;
        isOnlineRestored.value = true;
        _stopRecoveryTimer();
        Future.delayed(const Duration(seconds: 4), () {
          isOnlineRestored.value = false;
        });
      }
      _ensureSuccess(res);
      return res;
    } catch (e) {
      _handleNetworkError(e);
      rethrow;
    }
  }

  static Future<http.Response> postMultipart(
    String url,
    Map<String, dynamic> body, {
    Map<String, String>? files,
  }) async {
    try {
      final baseUrl = await serverUrl;
      final uri = Uri.parse('$baseUrl$apiVersion$url');
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('auth_token');

      final request = http.MultipartRequest('POST', uri);
      request.headers.addAll({
        'Accept': 'application/json',
        if (token != null && token.isNotEmpty) 'Authorization': 'Bearer $token',
      });

      body.forEach((key, value) {
        if (value != null) {
          request.fields[key] = value.toString();
        }
      });

      if (files != null) {
        for (var entry in files.entries) {
          if (entry.value.isNotEmpty) {
            request.files.add(await http.MultipartFile.fromPath(entry.key, entry.value));
          }
        }
      }

      final streamedResponse = await request.send();
      final res = await http.Response.fromStream(streamedResponse);
      if (isOffline.value) {
        isOffline.value = false;
        isOnlineRestored.value = true;
        _stopRecoveryTimer();
        Future.delayed(const Duration(seconds: 4), () {
          isOnlineRestored.value = false;
        });
      }
      _ensureSuccess(res);
      return res;
    } catch (e) {
      _handleNetworkError(e);
      rethrow;
    }
  }

  static void _handleNetworkError(Object error) {
    final str = error.toString();
    if (str.contains('Failed to fetch') ||
        str.contains('SocketException') ||
        str.contains('ClientException') ||
        str.contains('HandshakeException') ||
        str.contains('Failed host lookup') ||
        str.contains('Connection refused') ||
        str.contains('Connection closed')) {
      isOffline.value = true;
    }
  }

  static void _ensureSuccess(dynamic res) {
    if (res is http.Response) {
      if (res.statusCode == 401) {
        _handleUnauthorized();
      }
      if (res.statusCode < 200 || res.statusCode >= 300) {
        throw Exception(extractErrorMessage(res));
      }
    }
  }

  static void _handleUnauthorized() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');

    if (navigatorKey.currentState != null) {
      navigatorKey.currentState!.pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const LoginPage()),
        (route) => false,
      );
    }
  }

  static String extractErrorMessage(dynamic error) {
    if (error == null) return 'Ndodhi një gabim i papritur në server.';

    final str = error.toString();

    if (str.contains('Failed to fetch') ||
        str.contains('SocketException') ||
        str.contains('ClientException') ||
        str.contains('HandshakeException') ||
        str.contains('Failed host lookup') ||
        str.contains('NetworkImageLoadException') ||
        str.contains('Connection refused') ||
        str.contains('Connection closed')) {
      isOffline.value = true;
      return 'Nuk ka lidhje me internetin ose serveri është offline.';
    }

    if (error is http.Response) {
      try {
        final decoded = jsonDecode(error.body);
        if (decoded is Map && decoded.containsKey('message')) {
          return decoded['message'].toString();
        }
      } catch (_) {}
      if (error.statusCode == 403) return 'Ju nuk keni leje për këtë veprim.';
      if (error.statusCode == 404) return 'Përmbajtja e kërkuar nuk u gjet.';
      if (error.statusCode >= 500) return 'Serveri ka një problem të përkohshëm (500).';
    }

    if (str.startsWith('Exception: ')) {
      return str.substring(11);
    }

    return str;
  }
}
