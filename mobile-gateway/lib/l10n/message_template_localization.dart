import 'package:flutter/widgets.dart';
import 'message_template_en.dart';
import 'message_template_sq.dart';

String message_templateTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
    if (language == 'en') return message_templateEn;
    if (language == 'sq') return message_templateSq;
    return message_templateEn;
  }

  final source = getSource();
  var value = source[key] ?? message_templateEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return value;
}