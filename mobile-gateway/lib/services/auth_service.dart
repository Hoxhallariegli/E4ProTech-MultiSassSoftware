import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'api_service.dart';

class AuthService extends ChangeNotifier {
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
    notifyListeners();
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

  List<Map<String, dynamic>> get accessibleShops {
    final shops = _userData?['accessible_shops'];
    if (shops is List) {
      return List<Map<String, dynamic>>.from(shops.map((s) => Map<String, dynamic>.from(s)));
    }
    return [];
  }

  int? get currentBarberShopId => _userData?['barber_shop_id'];

  Future<void> sync() async {
    try {
      final response = await ApiService.get('/me');
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        _userData = data['user'];
        _permissions = List<String>.from(_userData?['permissions'] ?? []);

        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('user_data', jsonEncode(_userData));
        notifyListeners();
      }
    } catch (e) {
      // Silent fail or handle error
    }
  }

  Future<void> switchShop(int shopId) async {
    final response = await ApiService.post('/switch-shop', {'barber_shop_id': shopId});
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      _userData = data['user'];
      _permissions = List<String>.from(_userData?['permissions'] ?? []);

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('user_data', jsonEncode(_userData));
      notifyListeners();
    } else {
      throw Exception('Dështoi ndërrimi i dyqanit.');
    }
  }

  Future<void> logout() async {
    _userData = null;
    _permissions = [];
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('user_data');
    notifyListeners();
  }
}
