import 'package:flutter/widgets.dart';
import 'subscription_en.dart';
import 'subscription_sq.dart';

String subscriptionTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
    if (language == 'en') return subscriptionEn;
    if (language == 'sq') return subscriptionSq;
    return subscriptionEn;
  }

  final source = getSource();
  var value = source[key] ?? subscriptionEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return value;
}