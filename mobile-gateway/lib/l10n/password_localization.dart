import 'package:flutter/widgets.dart';
import 'password_en.dart';
import 'password_sq.dart';

String passwordTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();
  final source = language == 'sq' ? passwordSq : passwordEn;

  var value = source[key] ?? passwordEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{${name}}', replacement);
  });

  return value;
}