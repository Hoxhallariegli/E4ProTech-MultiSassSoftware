import 'package:flutter/widgets.dart';
import 'subscription_renewal_en.dart';
import 'subscription_renewal_sq.dart';

String subscription_renewalTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
    if (language == 'en') return subscription_renewalEn;
    if (language == 'sq') return subscription_renewalSq;
    return subscription_renewalEn;
  }

  final source = getSource();
  var value = source[key] ?? subscription_renewalEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return value;
}