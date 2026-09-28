import 'package:flutter/widgets.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';
import 'booking_en.dart';
import 'booking_sq.dart';

String bookingTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
    if (language == 'en') return bookingEn;
    if (language == 'sq') return bookingSq;
    return bookingEn;
  }

  final source = getSource();
  var value = source[key] ?? bookingEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return applyDynamicBranding(context, value);
}
