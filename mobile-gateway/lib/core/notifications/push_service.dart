import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';

final FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin = FlutterLocalNotificationsPlugin();

@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  WidgetsFlutterBinding.ensureInitialized();
  if (!kIsWeb) await Firebase.initializeApp();
}

class PushService {
  static bool _initialized = false;

  static Future<void> initialize() async {
    if (_initialized) return;
    try {
      if (!kIsWeb) {
        await Firebase.initializeApp();
        FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);

        const initSettings = InitializationSettings(
          android: AndroidInitializationSettings('@mipmap/ic_launcher'),
        );
        await flutterLocalNotificationsPlugin.initialize(initSettings);

        final androidImpl = flutterLocalNotificationsPlugin
            .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();
        if (androidImpl != null) {
          await androidImpl.createNotificationChannel(
            const AndroidNotificationChannel(
              'high_importance_channel',
              'Njoftime Kryesore',
              description: 'Kanal kryesor për njoftimet Push të E4ProTech Engine',
              importance: Importance.max,
            ),
          );
        }

        NotificationSettings settings = await FirebaseMessaging.instance.requestPermission(
          alert: true,
          badge: true,
          sound: true,
          provisional: false,
        );
        debugPrint('FCM Authorization Status: ${settings.authorizationStatus}');

        // Listen for token refreshes automatically
        FirebaseMessaging.instance.onTokenRefresh.listen((fcmToken) {
          debugPrint('FCM Token refreshed: $fcmToken');
          registerTokenWithBackend();
        });

        await registerTokenWithBackend();

        FirebaseMessaging.onMessage.listen((message) {
          debugPrint('FCM Foreground Message: ${message.notification?.title} - ${message.notification?.body}');
          if (message.notification != null) {
            flutterLocalNotificationsPlugin.show(
              message.hashCode,
              message.notification!.title,
              message.notification!.body,
              const NotificationDetails(
                android: AndroidNotificationDetails(
                  'high_importance_channel',
                  'Njoftime Kryesore',
                  importance: Importance.max,
                  priority: Priority.high,
                  icon: '@mipmap/ic_launcher',
                ),
              ),
            );
          }
        });

        _initialized = true;
      }
    } catch (e) {
      debugPrint('PushService initialization error: $e');
    }
  }

  static Future<void> subscribeToShop(int shopId) async {
    try {
      await FirebaseMessaging.instance.subscribeToTopic('shop_$shopId');
      debugPrint('Subscribed to FCM topic: shop_$shopId');
    } catch (e) {
      debugPrint('FCM topic subscription error: $e');
    }
  }

  static Future<void> unsubscribeFromShop(int shopId) async {
    try {
      await FirebaseMessaging.instance.unsubscribeFromTopic('shop_$shopId');
      debugPrint('Unsubscribed from FCM topic: shop_$shopId');
    } catch (e) {
      debugPrint('FCM topic un-subscription error: $e');
    }
  }

  static Future<void> registerTokenWithBackend() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final authToken = prefs.getString('auth_token');
      if (authToken == null || authToken.isEmpty) {
        debugPrint('Skip FCM registration: User not logged in.');
        return;
      }

      // Retry loop to ensure FCM token is generated after permission approval
      String? token;
      for (int i = 0; i < 4; i++) {
        try {
          token = await FirebaseMessaging.instance.getToken().timeout(const Duration(seconds: 4));
          if (token != null && token.isNotEmpty) break;
        } catch (e) {
          debugPrint('FCM getToken attempt $i error: $e');
        }
        await Future.delayed(const Duration(milliseconds: 600));
      }

      debugPrint('FCM Token fetched: $token');

      if (token != null && token.isNotEmpty) {
        final platform = defaultTargetPlatform == TargetPlatform.iOS ? 'ios' : 'android';
        final userName = AuthService.instance.user?['name'] ?? 'User';

        final res = await ApiService.post('/device-tokens/save-web-token', {
          'fcm_token': token,
          'platform': platform,
          'device_name': 'Mobile Device ($userName)',
        }).timeout(const Duration(seconds: 6));

        debugPrint('FCM Token registered with Laravel backend! Status: ${res.statusCode}');

        final rawShopId = AuthService.instance.user?['barber_shop_id'] ?? AuthService.instance.user?['business']?['id'];
        if (rawShopId != null) {
          final shopId = int.tryParse(rawShopId.toString());
          if (shopId != null) {
            await subscribeToShop(shopId);
          }
        }
      }
    } catch (e) {
      debugPrint('FCM token registration with backend error: $e');
    }
  }
}
