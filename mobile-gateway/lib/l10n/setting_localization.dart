import 'package:flutter/widgets.dart';
import 'setting_en.dart';
import 'setting_sq.dart';

String settingTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();
  final source = language == 'sq' ? settingSq : settingEn;

  var value = source[key] ?? settingEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{${name}}', replacement);
  });

  return value;
}