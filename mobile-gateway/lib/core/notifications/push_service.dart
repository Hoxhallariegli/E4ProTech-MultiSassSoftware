import 'dart:convert';
import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:flutter_background_service/flutter_background_service.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';
import 'package:telephony/telephony.dart';

final FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin = FlutterLocalNotificationsPlugin();

Future<void> _reportSmsStatus(String smsId, String status, {String? error, String? phone, String? body}) async {
  try {
    final prefs = await SharedPreferences.getInstance();
    if (phone != null) {
      List<String> logs = prefs.getStringList('sms_logs') ?? [];
      logs.insert(0, jsonEncode({'phone': phone, 'body': body ?? '', 'status': status, 'time': DateTime.now().toIso8601String(), 'error': error ?? ''}));
      if (logs.length > 30) logs = logs.sublist(0, 30);
      await prefs.setStringList('sms_logs', logs);
    }

    await ApiService.post('/sms-gateway/mark-sent', {
      'id': smsId,
      'status': status,
      'error_message': error,
    });
  } catch (e) {
    debugPrint('Error reporting SMS status to backend: $e');
  }
}

@pragma('vm:entry-point')
void onStart(ServiceInstance service) async {
  WidgetsFlutterBinding.ensureInitialized();
  if (!kIsWeb) {
    try {
      await Firebase.initializeApp();
    } catch (_) {}
  }

  service.on('execute_sms').listen((data) async {
    if (data == null) return;
    final String smsId = data['sms_id'] ?? '';
    if (smsId.isEmpty) return;

    final prefs = await SharedPreferences.getInstance();
    final String lastId = prefs.getString('last_processed_sms_id') ?? '';
    if (lastId == smsId) return; // Prevent duplicate sending for exact same SMS ID

    await prefs.setString('last_processed_sms_id', smsId);

    final String phone = data['phone'] ?? '';
    final String body = data['body'] ?? '';
    final int notifId = data['notif_id'] ?? 0;

    debugPrint('🚀 [Background Service] Executing SMS send to $phone for SMS #$smsId');

    try {
      if (!kIsWeb) {
        final Telephony telephony = Telephony.instance;
        await telephony.sendSms(to: phone, message: body);
      }
      await _reportSmsStatus(smsId, 'sent', phone: phone, body: body);
      await flutterLocalNotificationsPlugin.show(
        notifId,
        'SMS U Dërgua ✅',
        'Për: $phone',
        const NotificationDetails(
          android: AndroidNotificationDetails('sms_gateway', 'SMS Gateway Monitor', importance: Importance.low),
        ),
      );
      Timer(const Duration(seconds: 3), () => flutterLocalNotificationsPlugin.cancel(notifId));
    } catch (e) {
      debugPrint('❌ SMS send failed: $e');
      await _reportSmsStatus(smsId, 'failed', error: e.toString(), phone: phone, body: body);
    }
  });
}

@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  WidgetsFlutterBinding.ensureInitialized();
  if (!kIsWeb) {
    try {
      await Firebase.initializeApp();
    } catch (_) {}

    final String? smsId = message.data['sms_id'];
    if (smsId != null && smsId.isNotEmpty) {
      final prefs = await SharedPreferences.getInstance();
      final String lastId = prefs.getString('last_processed_sms_id') ?? '';
      if (lastId == smsId) return;

      if (message.data['action'] == 'SEND_SMS') {
        Map<String, dynamic> taskData = Map<String, dynamic>.from(message.data);
        taskData['notif_id'] = message.hashCode;
        FlutterBackgroundService().invoke("execute_sms", taskData);
      }
    }
  }
}

class PushService {
  static bool _initialized = false;

  static Future<void> initializeService() async {
    if (kIsWeb) return;
    try {
      final service = FlutterBackgroundService();
      await service.configure(
        androidConfiguration: AndroidConfiguration(
          onStart: onStart,
          autoStart: true,
          isForegroundMode: true,
          notificationChannelId: 'sms_gateway',
          initialNotificationTitle: 'SMS Gateway Aktiv 📱',
          initialNotificationContent: 'Aplikacioni po monitoron dërgimin e mesazheve...',
        ),
        iosConfiguration: IosConfiguration(autoStart: true),
      );
    } catch (e) {
      debugPrint('Error initializing background service: $e');
    }
  }

  static Future<void> initialize() async {
    if (_initialized) return;
    try {
      if (!kIsWeb) {
        try {
          await Firebase.initializeApp();
        } catch (_) {}

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
          await androidImpl.createNotificationChannel(
            const AndroidNotificationChannel(
              'sms_gateway',
              'SMS Gateway Monitor',
              description: 'Kanal për monitorimin e dërgimit të SMS',
              importance: Importance.low,
            ),
          );
        }

        await initializeService();

        NotificationSettings settings = await FirebaseMessaging.instance.requestPermission(
          alert: true,
          badge: true,
          sound: true,
          provisional: false,
        );
        debugPrint('FCM Authorization Status: ${settings.authorizationStatus}');

        FirebaseMessaging.instance.onTokenRefresh.listen((fcmToken) {
          debugPrint('FCM Token refreshed: $fcmToken');
          registerTokenWithBackend();
        });

        registerTokenWithBackend();

        FirebaseMessaging.onMessage.listen((message) async {
          debugPrint('FCM Foreground Message: ${message.notification?.title} - ${message.notification?.body}');

          if (message.data['action'] == 'SEND_SMS') {
            Map<String, dynamic> taskData = Map<String, dynamic>.from(message.data);
            taskData['notif_id'] = message.hashCode;
            FlutterBackgroundService().invoke("execute_sms", taskData);
            return;
          }

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
    } catch (e) {
      debugPrint('FCM topic subscription error: $e');
    }
  }

  static Future<void> registerTokenWithBackend() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final authToken = prefs.getString('auth_token');
      if (authToken == null || authToken.isEmpty) return;

      String? token;
      try {
        token = await FirebaseMessaging.instance.getToken();
      } catch (_) {}

      if (token != null && token.isNotEmpty) {
        final platform = defaultTargetPlatform == TargetPlatform.iOS ? 'ios' : 'android';
        final userName = AuthService.instance.user?['name'] ?? 'User';
        final rawShopId = AuthService.instance.user?['barber_shop_id'] ?? AuthService.instance.user?['business']?['id'];

        await ApiService.post('/device-tokens/save-web-token', {
          'fcm_token': token,
          'platform': platform,
          'device_name': 'Mobile Device ($userName)',
          if (rawShopId != null) 'barber_shop_id': rawShopId,
        });

        if (rawShopId != null) {
          final shopId = int.tryParse(rawShopId.toString());
          if (shopId != null) {
            await subscribeToShop(shopId);
          }
        }
      }
    } catch (e) {
      debugPrint('FCM token registration error: $e');
    }
  }
}
