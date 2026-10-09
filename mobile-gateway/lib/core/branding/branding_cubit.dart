import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:shared_preferences/shared_preferences.dart';

class BrandingState {
  final String appName;
  final String? logoUrl;
  final Color primaryColor;
  final bool smsEnabled;
  final int reminderHours;
  final int trialDaysLeft;
  final String planName;
  final String subscriptionStatus;
  final String businessType;
  final String staffLabel;
  final String staffLabelPlural;
  final String shopLabel;
  final String serviceLabel;

  const BrandingState({
    required this.appName,
    this.logoUrl,
    required this.primaryColor,
    this.smsEnabled = false,
    this.reminderHours = 2,
    this.trialDaysLeft = 0,
    this.planName = 'Plani Pro',
    this.subscriptionStatus = 'active',
    this.businessType = 'general',
    this.staffLabel = 'Punonjësi',
    this.staffLabelPlural = 'Punonjësit',
    this.shopLabel = 'Dyqanet',
    this.serviceLabel = 'Shërbimi',
  });

  bool get isExpired => subscriptionStatus == 'expired' || trialDaysLeft <= 0;

  factory BrandingState.defaultBranding() {
    return const BrandingState(
      appName: 'LaraFlutter Gateway',
      primaryColor: Color(0xFF3B82F6),
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'appName': appName,
      'logoUrl': logoUrl,
      'reminderHours': reminderHours,
      'primaryColor': primaryColor.value,
      'smsEnabled': smsEnabled,
      'trialDaysLeft': trialDaysLeft,
      'planName': planName,
      'subscriptionStatus': subscriptionStatus,
      'businessType': businessType,
      'staffLabel': staffLabel,
      'staffLabelPlural': staffLabelPlural,
      'shopLabel': shopLabel,
      'serviceLabel': serviceLabel,
    };
  }

    factory BrandingState.fromMap(Map<String, dynamic> map) {
    return BrandingState(
      appName: map['appName'] ?? 'LaraFlutter Gateway',
      logoUrl: map['logoUrl'],
      primaryColor: Color(map['primaryColor'] ?? 0xFF3B82F6),
      smsEnabled: map['smsEnabled'] ?? false,
      reminderHours: map['reminderHours'] ?? 2,
      trialDaysLeft: map['trialDaysLeft'] ?? 0,
      planName: map['planName'] ?? 'Plani Pro',
      subscriptionStatus: map['subscriptionStatus'] ?? 'active',
      businessType: map['businessType'] ?? 'general',
      staffLabel: map['staffLabel'] ?? 'Punonjësi',
      staffLabelPlural: map['staffLabelPlural'] ?? 'Punonjësit',
      shopLabel: map['shopLabel'] ?? 'Dyqanet',
      serviceLabel: map['serviceLabel'] ?? 'Shërbimi',
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
      reminderHours: businessData['reminder_hours_before'] ?? 2,
      trialDaysLeft: businessData['trial_days_left'] ?? 0,
      planName: businessData['plan_name'] ?? 'Plani Pro',
      subscriptionStatus: businessData['subscription_status'] ?? 'active',
      businessType: businessData['business_type'] ?? 'general',
      staffLabel: (businessData['staff_label'] != null && businessData['staff_label'].toString().isNotEmpty)
          ? businessData['staff_label'].toString()
          : 'Punonjësi',
      staffLabelPlural: (businessData['staff_label_plural'] != null && businessData['staff_label_plural'].toString().isNotEmpty)
          ? businessData['staff_label_plural'].toString()
          : 'Punonjësit',
      shopLabel: (businessData['shop_label'] != null && businessData['shop_label'].toString().isNotEmpty)
          ? businessData['shop_label'].toString()
          : 'Dyqanet',
      serviceLabel: (businessData['service_label'] != null && businessData['service_label'].toString().isNotEmpty)
          ? businessData['service_label'].toString()
          : 'Shërbimi',
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

extension BrandingContextExtension on BuildContext {
  BrandingState get branding => watch<BrandingCubit>().state;

  String get staffLabel {
    final isEn = Localizations.localeOf(this).languageCode == 'en';
    final state = watch<BrandingCubit>().state;
    if (state.staffLabel.isNotEmpty && state.staffLabel != 'Punonjësi' && state.staffLabel != 'Berber') {
      return state.staffLabel;
    }
    return isEn ? 'Staff Member' : 'Punonjësi';
  }

  String get staffLabelPlural {
    final isEn = Localizations.localeOf(this).languageCode == 'en';
    final state = watch<BrandingCubit>().state;
    if (state.staffLabelPlural.isNotEmpty && state.staffLabelPlural != 'Punonjësit' && state.staffLabelPlural != 'Berberët') {
      return state.staffLabelPlural;
    }
    return isEn ? 'Staff Members' : 'Punonjësit';
  }

  String get shopLabel {
    final isEn = Localizations.localeOf(this).languageCode == 'en';
    final state = watch<BrandingCubit>().state;
    if (state.shopLabel.isNotEmpty && state.shopLabel != 'Dyqanet' && state.shopLabel != 'Sallonet') {
      return state.shopLabel;
    }
    return isEn ? 'Salons & Shops' : 'Dyqanet';
  }

  String get serviceLabel {
    final isEn = Localizations.localeOf(this).languageCode == 'en';
    final state = watch<BrandingCubit>().state;
    if (state.serviceLabel.isNotEmpty && state.serviceLabel != 'Shërbimi') {
      return state.serviceLabel;
    }
    return isEn ? 'Service' : 'Shërbimi';
  }
}

String applyDynamicBranding(BuildContext context, String text) {
  try {
    final isEn = Localizations.localeOf(context).languageCode == 'en';
    final state = context.read<BrandingCubit>().state;
    final staffLabel = context.staffLabel;
    final staffLabelPlural = context.staffLabelPlural;
    final shopLabel = context.shopLabel;

    return text
        .replaceAll('Barber Shop Id', shopLabel)
        .replaceAll('Barber Shop ID', shopLabel)
        .replaceAll('Barber Shop', shopLabel)
        .replaceAll('BarberShop', shopLabel)
        .replaceAll('BarberShops', isEn ? 'Salons' : '${shopLabel}t')
        .replaceAll('Barber Shops', isEn ? 'Salons' : '${shopLabel}t')
        .replaceAll('BARBERSHOP', shopLabel.toUpperCase())
        .replaceAll('BARBER', staffLabelPlural.toUpperCase())
        .replaceAll('Barber Id', staffLabel)
        .replaceAll('Barber ID', staffLabel)
        .replaceAll('Barbers', staffLabelPlural)
        .replaceAll('barbers', staffLabelPlural.toLowerCase())
        .replaceAll('Barber', staffLabel)
        .replaceAll('barber', staffLabel.toLowerCase())
        .replaceAll('Berberët', staffLabelPlural)
        .replaceAll('berberët', staffLabelPlural.toLowerCase())
        .replaceAll('BERBERËT', staffLabelPlural.toUpperCase())
        .replaceAll('BERBERI', staffLabel.toUpperCase())
        .replaceAll('BERBER', staffLabel.toUpperCase())
        .replaceAll('Berberi është i zënë', '$staffLabel është i zënë')
        .replaceAll('Berberi ka orar pushimi', '$staffLabel ka orar pushimi')
        .replaceAll('Berberin', '${staffLabel}in')
        .replaceAll('berberin', '${staffLabel.toLowerCase()}in')
        .replaceAll('Berberë', staffLabelPlural)
        .replaceAll('berberë', staffLabelPlural.toLowerCase())
        .replaceAll('Berberi:', '$staffLabel:')
        .replaceAll('berberi:', '${staffLabel.toLowerCase()}:')
        .replaceAll('Berberi', staffLabel)
        .replaceAll('berberi', staffLabel.toLowerCase())
        .replaceAll('Berber', staffLabel)
        .replaceAll('berber', staffLabel.toLowerCase())
        .replaceAll('Berberanat', isEn ? 'Salons' : '${shopLabel}t')
        .replaceAll('Berberana', shopLabel)
        .replaceAll('Berberanë', shopLabel);
  } catch (_) {
    return text;
  }
}
