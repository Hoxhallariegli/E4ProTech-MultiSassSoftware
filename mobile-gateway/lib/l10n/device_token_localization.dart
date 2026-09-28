import 'package:flutter/widgets.dart';
import 'device_token_en.dart';
import 'device_token_sq.dart';

String device_tokenTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
    if (language == 'en') return device_tokenEn;
    if (language == 'sq') return device_tokenSq;
    return device_tokenEn;
  }

  final source = getSource();
  var value = source[key] ?? device_tokenEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return value;
}