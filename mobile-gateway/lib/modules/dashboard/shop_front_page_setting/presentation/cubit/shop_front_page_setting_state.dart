sealed class ShopFrontPageSettingState {
  const ShopFrontPageSettingState();
}

final class ShopFrontPageSettingInitial extends ShopFrontPageSettingState {
  const ShopFrontPageSettingInitial();
}

final class ShopFrontPageSettingLoading extends ShopFrontPageSettingState {
  final List<Map<String, dynamic>> items;
  const ShopFrontPageSettingLoading([this.items = const []]);
}

final class ShopFrontPageSettingLoaded extends ShopFrontPageSettingState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const ShopFrontPageSettingLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class ShopFrontPageSettingSaving extends ShopFrontPageSettingState {
  const ShopFrontPageSettingSaving();
}

final class ShopFrontPageSettingSaved extends ShopFrontPageSettingState {
  final Map<String, dynamic> item;
  const ShopFrontPageSettingSaved(this.item);
}

final class ShopFrontPageSettingDeleting extends ShopFrontPageSettingState {
  final int id;
  const ShopFrontPageSettingDeleting(this.id);
}

final class ShopFrontPageSettingFailure extends ShopFrontPageSettingState {
  final String message;
  final List<Map<String, dynamic>> items;
  const ShopFrontPageSettingFailure(this.message, [this.items = const []]);
}