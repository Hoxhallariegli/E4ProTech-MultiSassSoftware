class MessageTemplateModel {
  final int? id;
  final int? barberShopId;
  final String? channel;
  final String? type;
  final String? content;

  const MessageTemplateModel({
    this.id,
    this.barberShopId,
    this.channel,
    this.type,
    this.content,
  });

  factory MessageTemplateModel.fromJson(Map<String, dynamic> json) => MessageTemplateModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      barberShopId: json['barber_shop_id'] == null ? null : int.tryParse(json['barber_shop_id'].toString()),
      channel: json['channel']?.toString(),
      type: json['type']?.toString(),
      content: json['content']?.toString(),
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'barber_shop_id': barberShopId,
      'channel': channel,
      'type': type,
      'content': content,
  };
}