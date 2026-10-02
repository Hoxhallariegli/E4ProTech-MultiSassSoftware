import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mobile_gateway/services/auth_service.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/core/widgets/app_scaffold.dart';
import 'package:mobile_gateway/modules/dashboard/presentation/widgets/shop_switcher_widget.dart';
import 'package:mobile_gateway/core/realtime/realtime_service.dart';
import 'package:mobile_gateway/l10n/message_queue_localization.dart';
import '../cubit/message_queue_cubit.dart';
import '../cubit/message_queue_state.dart';
import '../widgets/message_queue_card.dart';
import '../../data/message_queue_repository.dart';
import 'message_queue_form_page.dart';

class MessageQueueListPage extends StatelessWidget {
  const MessageQueueListPage({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (_) => MessageQueueCubit(MessageQueueRepository())..load(),
      child: const _MessageQueueListView(),
    );
  }
}

class _MessageQueueListView extends StatefulWidget {
  const _MessageQueueListView();
  @override State<_MessageQueueListView> createState() => _MessageQueueListViewState();
}

class _MessageQueueListViewState extends State<_MessageQueueListView> {
  final _search = TextEditingController();
  final _scroll = ScrollController();
  final _filters = <String, dynamic>{};
  final repository = MessageQueueRepository();
  List<Map<String, dynamic>> _bookingIdFilterOptions = [];


  @override
  void initState() {
    super.initState();
    _loadFilterOptions();
    _scroll.addListener(() {
      if (_scroll.position.pixels >= _scroll.position.maxScrollExtent - 320) {
        context.read<MessageQueueCubit>().loadMore();
      }
    });
    RealtimeService.instance.subscribe('message-queues', (action, data) {
      if (!mounted) return;
      context.read<MessageQueueCubit>().handleRealtime(action, data);
    }, tenantId: AuthService.instance.user?['barber_shop_id']);
  }

  Future<void> _loadFilterOptions() async {
    _bookingIdFilterOptions = await repository.lookup('bookings');
    if (mounted) setState(() {});
  }

  @override
  void dispose() {
    _search.dispose();
    _scroll.dispose();
    super.dispose();
  }

  Future<void> _openFilters() async {
    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (_) => Padding(
        padding: EdgeInsets.fromLTRB(22, 14, 22, MediaQuery.of(context).viewInsets.bottom + 24),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
          const SizedBox(height: 20),
          Align(alignment: Alignment.centerLeft, child: Text(message_queueTr(context, 'list.filters'), style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800))),
          const SizedBox(height: 18),
          _FilterRelationDropdown(label: message_queueTr(context, 'field.booking_id'), value: _filters['booking_id']?.toString(), options: _bookingIdFilterOptions, onChanged: (v) => setState(() => _filters['booking_id'] = v)),
_FilterDropdown(label: message_queueTr(context, 'field.channel'), value: _filters['channel']?.toString(), options: ['sms', 'whatsapp'], onChanged: (v) => setState(() => _filters['channel'] = v)),
_FilterDropdown(label: message_queueTr(context, 'field.status'), value: _filters['status']?.toString(), options: ['pending', 'processing', 'sent', 'failed', 'skipped_limit'], onChanged: (v) => setState(() => _filters['status'] = v)),
          if (4 > 0) const SizedBox(height: 8),
          Row(children: [
            Expanded(child: OutlinedButton(onPressed: () { _filters.clear(); setState(() {}); Navigator.pop(context); context.read<MessageQueueCubit>().load(refresh: true, filters: {}); }, child: Text(message_queueTr(context, 'list.clear')))),
            const SizedBox(width: 12),
            Expanded(child: FilledButton(onPressed: () { Navigator.pop(context); context.read<MessageQueueCubit>().load(refresh: true, filters: _filters); }, child: Text(message_queueTr(context, 'list.apply')))),
          ]),
        ]),
      ),
    );
  }

  Future<void> _openForm([Map<String, dynamic>? item]) async {
    final changed = await Navigator.push<bool>(context, MaterialPageRoute(builder: (_) => MessageQueueFormPage(item: item)));
    if (changed == true && mounted) context.read<MessageQueueCubit>().load(refresh: true);
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final canAdd = AuthService.instance.hasPermission('add_message_queues');
    final canEdit = AuthService.instance.hasPermission('edit_message_queues');
    final canDelete = AuthService.instance.hasPermission('delete_message_queues');

    return AppScaffold(
      currentNavIndex: 1, // Modules tab
      title: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(message_queueTr(context, 'list.title'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
        InkWell(
          onTap: () {
              showModalBottomSheet(
                context: context,
                isScrollControlled: true,
                backgroundColor: Colors.transparent,
                builder: (_) => ShopSwitcherWidget(onSwitched: () => context.read<MessageQueueCubit>().refresh()),
              );
          },
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(AuthService.instance.user?['business']?['name'] ?? 'Select Shop', style: TextStyle(fontSize: 10, color: theme.colorScheme.primary, fontWeight: FontWeight.bold)),
              Icon(Icons.keyboard_arrow_down_rounded, size: 12, color: theme.colorScheme.primary),
            ],
          ),
        ),
      ]),
      actions: [
        IconButton(tooltip: message_queueTr(context, 'list.filters'), onPressed: 4 > 0 ? _openFilters : null, icon: const Icon(Icons.tune_rounded)),
        IconButton(tooltip: message_queueTr(context, 'list.refresh'), onPressed: () => context.read<MessageQueueCubit>().refresh(), icon: const Icon(Icons.refresh_rounded)),
        const SizedBox(width: 8),
      ],
      floatingActionButton: canAdd ? FloatingActionButton.extended(onPressed: () => _openForm(), icon: const Icon(Icons.add_rounded), label: Text(message_queueTr(context, 'list.add'))) : null,
      body: Column(children: [
        Padding(padding: const EdgeInsets.fromLTRB(20, 8, 20, 12), child: PremiumSearchBar(
          controller: _search,
          hintText: message_queueTr(context, 'list.search_hint'),
          onChanged: (v) => context.read<MessageQueueCubit>().search(v),
        )),
        Expanded(child: BlocConsumer<MessageQueueCubit, MessageQueueState>(
          listener: (context, state) {
            if (state is MessageQueueFailure) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(state.message), behavior: SnackBarBehavior.floating));
            if (state is MessageQueueSaved) Navigator.of(context).pop(true);
          },
          builder: (context, state) {
            final items = state is MessageQueueLoaded ? state.items : state is MessageQueueLoading ? state.items : state is MessageQueueFailure ? state.items : const <Map<String,dynamic>>[];
            if (state is MessageQueueLoading && items.isEmpty) return ListView.separated(padding: const EdgeInsets.all(20), itemCount: 7, separatorBuilder: (_, __) => const SizedBox(height: 12), itemBuilder: (_, __) => PremiumSkeleton());
            if (items.isEmpty) return PremiumEmptyState(title: _search.text.isEmpty ? message_queueTr(context, 'list.nothing') : message_queueTr(context, 'list.no_results'), message: _search.text.isEmpty ? message_queueTr(context, 'list.create_first') : message_queueTr(context, 'list.try_different'), icon: _search.text.isEmpty ? Icons.inbox_rounded : Icons.search_off_rounded, actionLabel: _search.text.isEmpty && canAdd ? message_queueTr(context, 'list.create_record') : null, action: _search.text.isEmpty && canAdd ? () => _openForm() : null);
            return RefreshIndicator(
              onRefresh: context.read<MessageQueueCubit>().refresh,
              child: ListView.separated(
                controller: _scroll,
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.fromLTRB(20, 4, 20, 120),
                itemCount: items.length + 1,
                separatorBuilder: (_, __) => const SizedBox(height: 10),
                itemBuilder: (context, index) {
                  if (index == items.length) return state is MessageQueueLoaded && state.hasMore ? const Padding(padding: EdgeInsets.all(22), child: Center(child: CircularProgressIndicator.adaptive())) : const SizedBox(height: 20);
                  return MessageQueueCard(
                    item: items[index],
                    onTap: () => _openForm(items[index]),
                    onDelete: canDelete ? () => _confirmDelete(context, items[index]) : null,
                  );
                },
              ),
            );
          },
        )),
      ]),
    );
  }

  Future<void> _confirmDelete(BuildContext context, Map<String, dynamic> item) async {
    final ok = await PremiumDialog.confirm(
      context,
      title: message_queueTr(context, 'list.delete_confirm_title'),
      message: message_queueTr(context, 'list.delete_confirm_message', {'id': item['id'].toString()}),
      confirmLabel: message_queueTr(context, 'list.delete'),
      destructive: true,
    );
    if (ok == true && context.mounted) context.read<MessageQueueCubit>().delete(item['id']);
  }
}

class _PremiumLoadingList extends StatelessWidget {
  const _PremiumLoadingList();
  @override Widget build(BuildContext context) => ListView.separated(padding: const EdgeInsets.all(20), itemCount: 7, separatorBuilder: (_, __) => const SizedBox(height: 12), itemBuilder: (_, __) => Container(height: 92, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surfaceContainerHighest, borderRadius: BorderRadius.circular(22))));
}

class _EmptyState extends StatelessWidget {
  final VoidCallback onAdd; final String query;
  const _EmptyState({required this.onAdd, required this.query});
  @override Widget build(BuildContext context) => Center(child: Padding(padding: const EdgeInsets.all(32), child: Column(mainAxisSize: MainAxisSize.min, children: [
    Container(width: 76, height: 76, decoration: BoxDecoration(color: Theme.of(context).colorScheme.primaryContainer, shape: BoxShape.circle), child: Icon(query.isEmpty ? Icons.inbox_rounded : Icons.search_off_rounded, size: 34)),
    const SizedBox(height: 18), Text(query.isEmpty ? message_queueTr(context, 'list.nothing') : message_queueTr(context, 'list.no_results'), style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w800)),
    const SizedBox(height: 7), Text(query.isEmpty ? message_queueTr(context, 'list.create_first') : message_queueTr(context, 'list.try_different'), textAlign: TextAlign.center, style: TextStyle(color: Colors.grey)),
    if (query.isEmpty) ...[const SizedBox(height: 18), FilledButton.icon(onPressed: onAdd, icon: const Icon(Icons.add_rounded), label: Text(message_queueTr(context, 'list.create_record')))],
  ])));
}

class _FilterText extends StatelessWidget {
  final String label, value; final ValueChanged<String> onChanged;
  const _FilterText({required this.label, required this.value, required this.onChanged});
  @override Widget build(BuildContext context) => Padding(padding: const EdgeInsets.only(bottom: 12), child: TextFormField(initialValue: value, onChanged: onChanged, decoration: InputDecoration(labelText: label, border: const OutlineInputBorder())));
}

class _FilterRelationDropdown extends StatelessWidget {
  final String label;
  final String? value;
  final List<Map<String, dynamic>> options;
  final ValueChanged<String?> onChanged;
  const _FilterRelationDropdown({required this.label, required this.value, required this.options, required this.onChanged});

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(bottom: 12),
    child: DropdownButtonFormField<String>(
      value: options.any((e) => e['id']?.toString() == value) ? value : null,
      items: options.map((e) => DropdownMenuItem<String>(
        value: e['id']?.toString(),
        child: Text((e['name'] ?? e['title'] ?? 'ID: ' + (e['id']?.toString() ?? '')).toString(), overflow: TextOverflow.ellipsis),
      )).toList(),
      onChanged: onChanged,
      isExpanded: true,
      decoration: InputDecoration(labelText: label, border: const OutlineInputBorder()),
    ),
  );
}

class _FilterDropdown extends StatelessWidget {
  final String label; final String? value; final List<String> options; final ValueChanged<String?> onChanged;
  const _FilterDropdown({required this.label, required this.value, required this.options, required this.onChanged});
  @override Widget build(BuildContext context) => Padding(padding: const EdgeInsets.only(bottom: 12), child: DropdownButtonFormField<String>(value: value, items: options.map((e) => DropdownMenuItem(value: e, child: Text(e))).toList(), onChanged: onChanged, decoration: InputDecoration(labelText: label, border: const OutlineInputBorder())));
}
