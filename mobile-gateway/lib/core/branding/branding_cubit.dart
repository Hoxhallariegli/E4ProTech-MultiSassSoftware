import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:shared_preferences/shared_preferences.dart';

class BrandingState {
  final String appName;
  final String? logoUrl;
  final Color primaryColor;
  final bool smsEnabled;
  final int trialDaysLeft;

  const BrandingState({
    required this.appName,
    this.logoUrl,
    required this.primaryColor,
    this.smsEnabled = false,
    this.trialDaysLeft = 0,
  });

  factory BrandingState.defaultBranding() {
    return const BrandingState(
      appName: 'LaraFlutter Gateway',
      primaryColor: Color(0xFF3B82F6), // Tailwind Blue-500
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'appName': appName,
      'logoUrl': logoUrl,
      'primaryColor': primaryColor.value,
      'smsEnabled': smsEnabled,
      'trialDaysLeft': trialDaysLeft,
    };
  }

  factory BrandingState.fromMap(Map<String, dynamic> map) {
    return BrandingState(
      appName: map['appName'] ?? 'LaraFlutter Gateway',
      logoUrl: map['logoUrl'],
      primaryColor: Color(map['primaryColor'] ?? 0xFF3B82F6),
      smsEnabled: map['smsEnabled'] ?? false,
      trialDaysLeft: map['trialDaysLeft'] ?? 0,
    );
  }
}

class BrandingCubit extends Cubit<BrandingState> {
  BrandingCubit() : super(BrandingState.defaultBranding()) {
    _loadSavedBranding();
  }

  Future<void> _loadSavedBranding() async {
    final prefs = await SharedPreferences.getInstance();
    final data = prefs.getString('branding_data');
    if (data != null) {
      emit(BrandingState.fromMap(jsonDecode(data)));
    }
  }

  void updateBranding(Map<String, dynamic> businessData) async {
    final hexColor = businessData['color'] ?? '#3B82F6';
    final color = Color(int.parse(hexColor.replaceFirst('#', '0xFF')));

    final newState = BrandingState(
      appName: businessData['app_name'] ?? businessData['name'] ?? 'LaraFlutter Gateway',
      logoUrl: businessData['logo'],
      primaryColor: color,
      smsEnabled: businessData['sms_active'] ?? false,
      trialDaysLeft: businessData['trial_days_left'] ?? 0,
    );

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('branding_data', jsonEncode(newState.toMap()));
    emit(newState);
  }

  void reset() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('branding_data');
    emit(BrandingState.defaultBranding());
  }
}
