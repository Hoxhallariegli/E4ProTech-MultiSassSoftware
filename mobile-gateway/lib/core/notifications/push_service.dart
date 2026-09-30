import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';

final FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin = FlutterLocalNotificationsPlugin();

@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  WidgetsFlutterBinding.ensureInitialized();
  if (!kIsWeb) await Firebase.initializeApp();
}

class PushService {
  static Future<void> initialize() async {
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

        await registerTokenWithBackend();

        final rawShopId = AuthService.instance.user?['barber_shop_id'];
        if (rawShopId != null) {
          final shopId = int.tryParse(rawShopId.toString());
          if (shopId != null) {
            await subscribeToShop(shopId);
          }
        }

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
      final token = await FirebaseMessaging.instance.getToken();
      debugPrint('FCM Token fetched: $token');

      if (token != null && token.isNotEmpty) {
        final platform = defaultTargetPlatform == TargetPlatform.iOS ? 'ios' : 'android';
        final userName = AuthService.instance.user?['name'] ?? 'User';

        await ApiService.post('/device-tokens/save-web-token', {
          'fcm_token': token,
          'platform': platform,
          'device_name': 'Mobile Device ($userName)',
        });

        debugPrint('FCM Token registered with Laravel backend!');
      }
    } catch (e) {
      debugPrint('FCM token registration with backend error: $e');
    }
  }
}
