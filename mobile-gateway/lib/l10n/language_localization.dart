import 'package:flutter/widgets.dart';
import 'language_en.dart';
import 'language_sq.dart';

String languageTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();
  final source = language == 'sq' ? languageSq : languageEn;

  var value = source[key] ?? languageEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{${name}}', replacement);
  });

  return value;
}