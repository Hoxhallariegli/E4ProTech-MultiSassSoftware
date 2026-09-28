import 'package:flutter/widgets.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';
import 'service_en.dart';
import 'service_sq.dart';

String serviceTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
    if (language == 'en') return serviceEn;
    if (language == 'sq') return serviceSq;
    return serviceEn;
  }

  final source = getSource();
  var value = source[key] ?? serviceEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return applyDynamicBranding(context, value);
}
