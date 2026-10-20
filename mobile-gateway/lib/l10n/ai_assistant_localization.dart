import 'package:flutter/widgets.dart';
import 'ai_assistant_en.dart';
import 'ai_assistant_sq.dart';

String ai_assistantTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();
  final source = language == 'sq' ? ai_assistantSq : ai_assistantEn;

  var value = source[key] ?? ai_assistantEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return value;
}
