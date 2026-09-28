import 'package:flutter/widgets.dart';
import 'event_setting_en.dart';
import 'event_setting_sq.dart';

String event_settingTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
    if (language == 'en') return event_settingEn;
    if (language == 'sq') return event_settingSq;
    return event_settingEn;
  }

  final source = getSource();
  var value = source[key] ?? event_settingEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return value;
}