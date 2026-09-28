import 'package:flutter/widgets.dart';
import 'notification_channel_en.dart';
import 'notification_channel_sq.dart';

String notification_channelTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
    if (language == 'en') return notification_channelEn;
    if (language == 'sq') return notification_channelSq;
    return notification_channelEn;
  }

  final source = getSource();
  var value = source[key] ?? notification_channelEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return value;
}