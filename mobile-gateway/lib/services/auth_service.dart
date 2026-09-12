import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class AuthService {
  AuthService._();
  static final instance = AuthService._();

  Map<String, dynamic>? _userData;
  List<String> _permissions = [];

  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    final data = prefs.getString('user_data');
    if (data != null) {
      _userData = jsonDecode(data);
      _permissions = List<String>.from(_userData?['permissions'] ?? []);
    } else {
      _userData = null;
      _permissions = [];
    }
  }

  bool hasPermission(String permission) {
    if (_userData?['is_admin'] == true) return true;
    return _permissions.contains(permission);
  }

  bool hasAnyPermission(List<String> permissions) {
    if (_userData?['is_admin'] == true) return true;
    return permissions.any((p) => _permissions.contains(p));
  }

  Map<String, dynamic>? get user => _userData;
}
