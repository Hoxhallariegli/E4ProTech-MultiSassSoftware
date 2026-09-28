import 'package:flutter/widgets.dart';
import 'message_queue_en.dart';
import 'message_queue_sq.dart';

String message_queueTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
    if (language == 'en') return message_queueEn;
    if (language == 'sq') return message_queueSq;
    return message_queueEn;
  }

  final source = getSource();
  var value = source[key] ?? message_queueEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return value;
}