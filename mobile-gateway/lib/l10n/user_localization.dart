import 'package:flutter/widgets.dart';
import 'user_en.dart';
import 'user_sq.dart';

String userTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();
  final source = language == 'sq' ? userSq : userEn;

  var value = source[key] ?? userEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{${name}}', replacement);
  });

  return value;
}