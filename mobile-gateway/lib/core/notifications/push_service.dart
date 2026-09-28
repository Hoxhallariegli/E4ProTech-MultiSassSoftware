import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
}

class PushService {
  static Future<void> initialize() async {
    await Firebase.initializeApp();
    FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);
    await FirebaseMessaging.instance.requestPermission(alert: true, badge: true, sound: true);

    final token = await FirebaseMessaging.instance.getToken();
    if (token != null) {
      // TODO: send this token to your Laravel / device-tokens endpoint.
    }

    FirebaseMessaging.onMessage.listen((message) {
      // Realtime UI is handled by Reverb. FCM is for background/terminated delivery.
    });
  }
}