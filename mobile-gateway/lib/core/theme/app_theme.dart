import 'package:flutter/material.dart';
import 'app_colors.dart';
import 'app_radius.dart';
import 'app_typography.dart';

abstract final class AppTheme {
  static ThemeData light({Color seed = AppColors.primary}) {
    final scheme = ColorScheme.fromSeed(seedColor: seed, brightness: Brightness.light);
    return _build(scheme);
  }

  static ThemeData dark({Color seed = AppColors.primary}) {
    final scheme = ColorScheme.fromSeed(seedColor: seed, brightness: Brightness.dark);
    return _build(scheme);
  }

  static ThemeData _build(ColorScheme scheme) {
    final base = ThemeData(useMaterial3: true, colorScheme: scheme);
    return base.copyWith(
      scaffoldBackgroundColor: scheme.surface,
      textTheme: AppTypography.apply(base.textTheme),
      appBarTheme: AppBarTheme(
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: false,
        backgroundColor: scheme.surface,
        foregroundColor: scheme.onSurface,
        titleTextStyle: base.textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w800),
      ),
      cardTheme: CardThemeData(
        elevation: 0,
        margin: EdgeInsets.zero,
        color: scheme.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.lg)),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: scheme.surfaceContainerHighest.withValues(alpha: .45),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(AppRadius.md), borderSide: BorderSide.none),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(AppRadius.md), borderSide: BorderSide.none),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(AppRadius.md), borderSide: BorderSide(color: scheme.primary, width: 1.4)),
        errorBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(AppRadius.md), borderSide: BorderSide(color: scheme.error.withValues(alpha: .55))),
        focusedErrorBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(AppRadius.md), borderSide: BorderSide(color: scheme.error, width: 1.4)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          minimumSize: const Size(0, 50),
          elevation: 0,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
          textStyle: const TextStyle(fontWeight: FontWeight.w800),
        ),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          minimumSize: const Size(0, 50),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
          textStyle: const TextStyle(fontWeight: FontWeight.w800),
        ),
      ),
      chipTheme: base.chipTheme.copyWith(shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.pill))),
      dialogTheme: DialogThemeData(
        elevation: 0,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.xl)),
      ),
      bottomSheetTheme: BottomSheetThemeData(
        elevation: 0,
        shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.xl))),
      ),
    );
  }
}
