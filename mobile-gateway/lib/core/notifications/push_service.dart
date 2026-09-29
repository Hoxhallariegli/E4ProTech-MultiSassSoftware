import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';

@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
}

class PushService {
  static Future<void> initialize() async {
    try {
      await Firebase.initializeApp();
      FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);

      NotificationSettings settings = await FirebaseMessaging.instance.requestPermission(
        alert: true,
        badge: true,
        sound: true,
      );

      if (settings.authorizationStatus == AuthorizationStatus.authorized ||
          settings.authorizationStatus == AuthorizationStatus.provisional) {
        await registerTokenWithBackend();
      }

      try {
        await FirebaseMessaging.instance.subscribeToTopic('all');
      } catch (_) {}

      FirebaseMessaging.onMessage.listen((message) {
        debugPrint('FCM Foreground message received: ${message.notification?.title}');
      });
    } catch (e) {
      debugPrint('PushService initialization error: $e');
    }
  }

  static Future<void> registerTokenWithBackend() async {
    try {
      if (AuthService.instance.user == null) return;

      final token = await FirebaseMessaging.instance.getToken();
      if (token != null && token.isNotEmpty) {
        final platform = defaultTargetPlatform == TargetPlatform.iOS ? 'ios' : 'android';

        await ApiService.post('/device-tokens/save-web-token', {
          'fcm_token': token,
          'platform': platform,
          'device_name': 'Mobile Device (${AuthService.instance.user?['name'] ?? 'Staff'})',
        });
      }
    } catch (e) {
      debugPrint('FCM token registration with backend error: $e');
    }
  }
}
