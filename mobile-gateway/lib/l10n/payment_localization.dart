import 'package:flutter/widgets.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';
import 'payment_en.dart';
import 'payment_sq.dart';

String paymentTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
    if (language == 'en') return paymentEn;
    if (language == 'sq') return paymentSq;
    return paymentEn;
  }

  final source = getSource();
  var value = source[key] ?? paymentEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return applyDynamicBranding(context, value);
}
