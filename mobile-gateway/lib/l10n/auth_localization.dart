import 'package:flutter/widgets.dart';
import 'auth_en.dart';
import 'auth_sq.dart';

String authTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();
  final source = language == 'sq' ? authSq : authEn;

  var value = source[key] ?? authEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{${name}}', replacement);
  });

  return value;
}