import 'package:flutter/widgets.dart';
import 'message_log_en.dart';
import 'message_log_sq.dart';

String message_logTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
    if (language == 'en') return message_logEn;
    if (language == 'sq') return message_logSq;
    return message_logEn;
  }

  final source = getSource();
  var value = source[key] ?? message_logEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return value;
}