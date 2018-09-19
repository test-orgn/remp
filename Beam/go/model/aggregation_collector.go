package model

import (
	"fmt"
	"time"

	"github.com/olivere/elastic"
	"github.com/pkg/errors"
)

type ElasticAggregationCollector interface {
	// Label returns aggregation label to be used in histogramData extraction.
	Label(field string) string
	// Value extracts aggregation result and returns primary value of the aggregation.
	Value(field string) (float64, bool)
	// Aggregations returns resulting aggregations fed to collector.
	Aggregations() elastic.Aggregations
	// Value extracts aggregation result and returns primary value of the aggregation.
	HistogramItemValue(field string, histogramItem *elastic.AggregationBucketHistogramItem) (float64, error)
}

type HistogramCollector struct {
}

// Collect iterates histogram aggregation results and returns slice of histogram items.
func (hc *HistogramCollector) Collect(field string, eac ElasticAggregationCollector) ([]HistogramItem, error) {
	histogramData, ok := eac.Aggregations().DateHistogram("date_time_histogram")
	if !ok {
		return nil, errors.New("missing expected histogram aggregation data")
	}

	var histogram []HistogramItem
	for _, histogramItem := range histogramData.Buckets {
		time := time.Unix(0, int64(histogramItem.Key)*int64(time.Millisecond)).UTC()
		value, err := eac.HistogramItemValue(field, histogramItem)
		if err != nil {
			return nil, err
		}

		histogram = append(histogram, HistogramItem{
			Time:  time,
			Value: value,
		})
	}

	return histogram, nil
}

// ElasticCountCollector represents collector for Count aggregation.
type ElasticCountCollector struct {
	Aggs     elastic.Aggregations
	DocCount int64
}

// Aggregations returns resulting aggregations fed to collector.
func (esc ElasticCountCollector) Aggregations() elastic.Aggregations {
	return esc.Aggs
}

// Label returns aggregation label to be used in histogramData extraction.
func (esc ElasticCountCollector) Label(field string) string {
	return fmt.Sprintf("%s_Count", field)
}

// Value returns resulting value of aggregation.
func (esc ElasticCountCollector) Value(field string) (float64, bool) {
	return float64(esc.DocCount), true
}

// HistogramItemValue extracts aggregation result and returns primary value of the aggregation based on provided aggregation field.
func (esc ElasticCountCollector) HistogramItemValue(field string, histogramItem *elastic.AggregationBucketHistogramItem) (float64, error) {
	return float64(histogramItem.DocCount), nil
}

// ElasticSumCollector represents collector for Sum aggregation.
type ElasticSumCollector struct {
	Aggs elastic.Aggregations
}

// Aggregations returns resulting aggregations fed to collector.
func (esc ElasticSumCollector) Aggregations() elastic.Aggregations {
	return esc.Aggs
}

// Label returns aggregation label to be used in histogramData extraction.
func (esc ElasticSumCollector) Label(field string) string {
	return fmt.Sprintf("%s_sum", field)
}

// Value returns resulting value of aggregation.
func (esc ElasticSumCollector) Value(field string) (float64, bool) {
	label := esc.Label(field)
	sumAgg, ok := esc.Aggs.Sum(label)
	if !ok {
		return 0, false
	}
	if sumAgg.Value == nil {
		return 0, true
	}
	return float64(*sumAgg.Value), true
}

// HistogramItemValue extracts aggregation result and returns primary value of the aggregation based on provided aggregation field.
func (esc ElasticSumCollector) HistogramItemValue(field string, histogramItem *elastic.AggregationBucketHistogramItem) (float64, error) {
	label := esc.Label(field)
	agg, ok := histogramItem.Aggregations.Sum(label)
	if !ok {
		return 0, fmt.Errorf("cant find %s sub agg in date histogram agg", label)
	}
	return float64(*agg.Value), nil
}

// ElasticAvgCollector represents collector for Avg aggregation.
type ElasticAvgCollector struct {
	Aggs elastic.Aggregations
}

// Aggregations returns resulting aggregations fed to collector.
func (esc ElasticAvgCollector) Aggregations() elastic.Aggregations {
	return esc.Aggs
}

// Label returns aggregation label to be used in histogramData extraction.
func (esc ElasticAvgCollector) Label(field string) string {
	return fmt.Sprintf("%s_avg", field)
}

// Value returns resulting value of aggregation.
func (esc ElasticAvgCollector) Value(field string) (float64, bool) {
	label := esc.Label(field)
	avgAgg, ok := esc.Aggs.Avg(label)
	if !ok {
		return 0, false
	}
	if avgAgg.Value == nil {
		return 0, true
	}
	return float64(*avgAgg.Value), true
}

// HistogramItemValue extracts aggregation result and returns primary value of the aggregation based on provided aggregation field.
func (esc ElasticAvgCollector) HistogramItemValue(field string, histogramItem *elastic.AggregationBucketHistogramItem) (float64, error) {
	label := esc.Label(field)
	agg, ok := histogramItem.Aggregations.Avg(label)
	if !ok {
		return 0, fmt.Errorf("cant find %s sub agg in date histogram agg", label)
	}
	return float64(*agg.Value), nil
}

// ElasticCardinalityCollector represents collector for Cardinality aggregation.
type ElasticCardinalityCollector struct {
	Aggs elastic.Aggregations
}

// Aggregations returns resulting aggregations fed to collector.
func (esc ElasticCardinalityCollector) Aggregations() elastic.Aggregations {
	return esc.Aggs
}

// Label returns aggregation label to be used in histogramData extraction.
func (esc ElasticCardinalityCollector) Label(field string) string {
	return fmt.Sprintf("%s_Cardinality", field)
}

// Value returns resulting value of aggregation.
func (esc ElasticCardinalityCollector) Value(field string) (float64, bool) {
	label := esc.Label(field)
	cardinalityAgg, ok := esc.Aggs.Cardinality(label)
	if !ok {
		return 0, false
	}
	if cardinalityAgg.Value == nil {
		return 0, true
	}
	return float64(*cardinalityAgg.Value), true
}

// HistogramItemValue extracts aggregation result and returns primary value of the aggregation based on provided aggregation field.
func (esc ElasticCardinalityCollector) HistogramItemValue(field string, histogramItem *elastic.AggregationBucketHistogramItem) (float64, error) {
	label := esc.Label(field)
	agg, ok := histogramItem.Aggregations.Cardinality(label)
	if !ok {
		return 0, fmt.Errorf("cant find %s sub agg in date histogram agg", label)
	}
	return float64(*agg.Value), nil
}
