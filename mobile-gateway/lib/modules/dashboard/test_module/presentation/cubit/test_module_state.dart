sealed class TestModuleState {
  const TestModuleState();
}

final class TestModuleInitial extends TestModuleState {
  const TestModuleInitial();
}

final class TestModuleLoading extends TestModuleState {
  final List<Map<String, dynamic>> items;
  const TestModuleLoading([this.items = const []]);
}

final class TestModuleLoaded extends TestModuleState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const TestModuleLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class TestModuleSaving extends TestModuleState {
  const TestModuleSaving();
}

final class TestModuleSaved extends TestModuleState {
  final Map<String, dynamic> item;
  const TestModuleSaved(this.item);
}

final class TestModuleDeleting extends TestModuleState {
  final int id;
  const TestModuleDeleting(this.id);
}

final class TestModuleFailure extends TestModuleState {
  final String message;
  final List<Map<String, dynamic>> items;
  const TestModuleFailure(this.message, [this.items = const []]);
}