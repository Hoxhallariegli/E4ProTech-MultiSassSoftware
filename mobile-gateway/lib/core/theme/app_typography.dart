import 'package:flutter/material.dart';

abstract final class AppTypography {
  static TextTheme apply(TextTheme base) => base.copyWith(
        headlineSmall: base.headlineSmall?.copyWith(fontWeight: FontWeight.w800, letterSpacing: -.5),
        titleLarge: base.titleLarge?.copyWith(fontWeight: FontWeight.w800, letterSpacing: -.2),
        titleMedium: base.titleMedium?.copyWith(fontWeight: FontWeight.w700),
        bodyLarge: base.bodyLarge?.copyWith(fontWeight: FontWeight.w500),
        labelLarge: base.labelLarge?.copyWith(fontWeight: FontWeight.w700),
      );
}
