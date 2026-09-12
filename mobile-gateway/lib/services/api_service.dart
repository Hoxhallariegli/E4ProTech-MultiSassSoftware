import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../main.dart';
import '../modules/auth/presentation/pages/login_page.dart';

class ApiService {
  static String? _customUrl;

  static Future<String> get serverUrl async {
    if (_customUrl != null) return _customUrl!;
    final prefs = await SharedPreferences.getInstance();
    final savedUrl = prefs.getString('custom_api_base_url');
    if (savedUrl != null && savedUrl.isNotEmpty) {
      _customUrl = savedUrl;
      return savedUrl;
    }
    return const String.fromEnvironment('API_BASE_URL', defaultValue: 'http://10.10.12.14:5000');
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
    final baseUrl = await serverUrl;
    final uri = Uri.parse('$baseUrl$apiVersion$url');
    final headers = await _getHeaders();
    final res = await http.get(uri, headers: headers);
    _ensureSuccess(res);
    return res;
  }

  static Future<http.Response> post(String url, Map<String, dynamic> body) async {
    final baseUrl = await serverUrl;
    final uri = Uri.parse('$baseUrl$apiVersion$url');
    final headers = await _getHeaders();
    final res = await http.post(uri, headers: headers, body: jsonEncode(body));
    _ensureSuccess(res);
    return res;
  }

  static Future<http.Response> put(String url, Map<String, dynamic> body) async {
    final baseUrl = await serverUrl;
    final uri = Uri.parse('$baseUrl$apiVersion$url');
    final headers = await _getHeaders();
    final res = await http.put(uri, headers: headers, body: jsonEncode(body));
    _ensureSuccess(res);
    return res;
  }

  static Future<http.Response> delete(String url) async {
    final baseUrl = await serverUrl;
    final uri = Uri.parse('$baseUrl$apiVersion$url');
    final headers = await _getHeaders();
    final res = await http.delete(uri, headers: headers);
    _ensureSuccess(res);
    return res;
  }

  static Future<http.Response> postMultipart(
    String url,
    Map<String, dynamic> body, {
    Map<String, String>? files, // fieldName -> filePath
  }) async {
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
    return http.Response.fromStream(streamedResponse);
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

  static String extractErrorMessage(dynamic res) {
    try {
      if (res is http.Response) {
        final decoded = jsonDecode(res.body);
        if (decoded is Map && decoded.containsKey('message')) {
          return decoded['message'].toString();
        }
      }
    } catch (_) {}
    return 'An unexpected server error occurred.';
  }
}
