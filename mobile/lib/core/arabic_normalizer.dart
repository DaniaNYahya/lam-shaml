class ArabicNormalizer {
  static final RegExp _diacritics = RegExp(r'[\u064B-\u065F\u0670]');
  static final RegExp _tatweel = RegExp(r'\u0640');
  static final RegExp _multiSpace = RegExp(r'\s+');

  static String normalize(String input, {bool removeArticle = true}) {
    var value = input.trim().toLowerCase();
    value = value.replaceAll(_diacritics, '');
    value = value.replaceAll(_tatweel, '');
    value = value
        .replaceAll(RegExp('[أإآٱ]'), 'ا')
        .replaceAll('ى', 'ي')
        .replaceAll('ة', 'ه')
        .replaceAll('ؤ', 'و')
        .replaceAll('ئ', 'ي');
    value = value.replaceAll(_multiSpace, ' ').trim();
    if (removeArticle) {
      value = value
          .split(' ')
          .map((part) => part.startsWith('ال') && part.length > 3 ? part.substring(2) : part)
          .join(' ');
    }
    return value;
  }

  static bool sameNameParts(String first, String second) {
    final a = normalize(first).split(' ')..sort();
    final b = normalize(second).split(' ')..sort();
    return a.join(' ') == b.join(' ');
  }
}
